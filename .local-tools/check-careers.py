from playwright.sync_api import sync_playwright
import subprocess

def cli(code):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip()

try:
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width': 1440, 'height': 1000})
        errors = []
        page.on('pageerror', lambda error: errors.append(str(error)))
        page.goto('http://127.0.0.1:8085/', wait_until='networkidle')
        page.locator('.careers-banner').screenshot(path='.local-tools/careers-desktop.png')
        page.locator('.careers-secondary').click()
        page.wait_for_load_state('networkidle')
        assert 'Вакансии' in page.locator('.jobs-hero h1').inner_text()
        page.goto('http://127.0.0.1:8085/', wait_until='networkidle')
        page.set_viewport_size({'width': 390, 'height': 844})
        page.locator('.careers-banner').screenshot(path='.local-tools/careers-mobile.png')
        assert not page.evaluate('document.documentElement.scrollWidth > innerWidth')
        page.locator('.careers-apply').click()
        dialog = page.locator('#careers-dialog')
        assert dialog.is_visible()
        dialog.locator('[name="client_name"]').fill('CAREERS_BROWSER_CHECK')
        dialog.locator('[name="client_phone"]').fill('+7 (999) 123-45-67')
        dialog.locator('[name="message"]').fill('Тест формы вакансии')
        dialog.locator('[name="consent"]').check()
        dialog.get_by_role('button', name='Отправить отклик').click()
        page.wait_for_url('**/?application=saved#careers')
        assert page.locator('.careers-notice').inner_text() == 'Спасибо! Ваш отклик сохранён.'
        assert not errors, errors
        browser.close()
    result = cli("$posts=get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'CAREERS_BROWSER_CHECK']); echo count($posts); if ($posts) echo ':' . (str_contains($posts[0]->post_content, 'Отклик на вакансию') ? 'application' : 'wrong');")
    assert result == '1:application', result
    print('PASS: desktop/mobile, vacancy list, application dialog, submitted application saved to database.')
finally:
    cli("foreach(get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'CAREERS_BROWSER_CHECK']) as $post) { if (str_starts_with($post->post_title, 'CAREERS_BROWSER_CHECK —')) wp_delete_post($post->ID, true); }")
