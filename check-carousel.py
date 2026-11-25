from pathlib import Path
import re
import subprocess
from playwright.sync_api import sync_playwright

BASE = 'http://127.0.0.1:8085'
def cli(code):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip()

with sync_playwright() as p:
    browser = p.chromium.launch()
    page = browser.new_page(viewport={'width': 1440, 'height': 1000}, reduced_motion='reduce')
    errors = []
    page.on('pageerror', lambda error: errors.append(str(error)))
    page.goto(BASE, wait_until='networkidle')
    assert page.locator('.carousel-track>.card').count() == 3
    first = page.locator('.carousel-track>.card h3').first.inner_text()
    page.get_by_role('button', name='Следующая услуга').click()
    page.wait_for_timeout(100)
    assert page.locator('.carousel-track>.card h3').first.inner_text() != first
    page.get_by_role('button', name='Предыдущая услуга').click()
    page.wait_for_timeout(100)
    assert page.locator('.carousel-track>.card h3').first.inner_text() == first
    page.locator('.carousel-viewport').focus()
    page.keyboard.press('ArrowRight')
    page.wait_for_timeout(100)
    assert page.locator('.carousel-status').text_content() == '02 / 03'
    page.get_by_role('button', name='Показать услугу 3').click()
    assert page.locator('.carousel-status').text_content() == '03 / 03'
    page.get_by_role('button', name='Показать услугу 1').click()
    assert page.locator('.catalog-specs').count() == 3
    assert page.locator('.catalog-badge').count() == 3
    assert page.locator('.card-image img').evaluate_all('(images) => images.every(img => img.complete && img.naturalWidth > 0)')
    page.locator('#services').screenshot(path='.local-tools/carousel-desktop.png')
    page.set_viewport_size({'width': 390, 'height': 844})
    page.wait_for_timeout(150)
    assert page.evaluate('document.documentElement.scrollWidth <= innerWidth')
    assert page.locator('.carousel-track>.card[inert]').count() == 2
    page.locator('#services').screenshot(path='.local-tools/carousel-mobile.png')
    access = Path('local-access.txt').read_text(encoding='utf-8-sig')
    password = re.search(r'Пароль: (.+)', access).group(1).strip()
    page.goto(BASE + '/wp-login.php')
    page.locator('#user_login').fill('strojka')
    page.locator('#user_pass').fill(password)
    page.locator('#wp-submit').click()
    page.wait_for_url('**/wp-admin/**')
    page.goto(BASE + '/wp-admin/post-new.php?post_type=strojka_service')
    assert page.locator('.acf-field[data-name="service_description"]').count() == 1
    assert page.locator('.acf-field[data-name="service_image"]').count() == 1
    assert page.locator('.acf-field[data-name="service_order"]').count() == 1
    assert page.locator('.acf-field[data-name="service_price"]').count() == 1
    assert page.locator('.acf-field[data-name="service_badge"]').count() == 1
    assert not errors, errors
    browser.close()

test_id = cli("echo wp_insert_post(['post_type'=>'strojka_service','post_status'=>'publish','post_title'=>'CAROUSEL_TEST_ONLY']);")
try:
    cli("update_field('field_strojka_description', 'ACF_TEST_DESCRIPTION', " + test_id + "); update_field('field_strojka_order', -10, " + test_id + "); do_action('acf/save_post', " + test_id + ");")
    assert cli("echo strojka_services()[0]->ID;") == test_id
    import urllib.request
    assert 'ACF_TEST_DESCRIPTION' in urllib.request.urlopen(BASE).read().decode()
    cli("wp_trash_post(" + test_id + ");")
    assert 'ACF_TEST_DESCRIPTION' not in urllib.request.urlopen(BASE).read().decode()
finally:
    cli("wp_delete_post(" + test_id + ", true);")
print('PASS: carousel next/previous/keyboard, mobile layout, ACF admin fields, adding/editing/sorting/removing services; test content removed.')
