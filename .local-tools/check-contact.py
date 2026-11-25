import subprocess
from playwright.sync_api import sync_playwright

def cli(code):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip()

try:
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width': 1440, 'height': 1100})
        page.goto('http://127.0.0.1:8085/', wait_until='domcontentloaded')
        page.locator('.contact-section').scroll_into_view_if_needed()
        page.wait_for_timeout(2000)
        page.locator('.contact-section').screenshot(path='.local-tools/contact-desktop.png')
        assert page.locator('.contact-cards article').count() == 4
        assert page.locator('.contact-messengers .messenger-icon').count() == 2
        assert page.locator('.whatsapp-button').count() == 0
        assert page.locator('#strojka_nonce').count() == 1
        page.set_viewport_size({'width': 390, 'height': 844})
        page.locator('.contact-section').screenshot(path='.local-tools/contact-mobile.png')
        assert not page.evaluate('document.documentElement.scrollWidth > innerWidth')
        form = page.locator('#request')
        form.locator('[name="client_name"]').fill('CONTACT_BROWSER_CHECK')
        form.locator('[name="client_phone"]').fill('+7 (999) 123-45-67')
        form.locator('[name="district"]').fill('Заокский')
        form.locator('[name="message"]').fill('Проверка заявки инженеру')
        form.locator('[name="consent"]').check()
        form.get_by_role('button', name='Вызвать инженера на замер бесплатно').click()
        page.wait_for_url('**/?request=saved#request')
        assert form.get_by_role('status').inner_text() == 'Спасибо! Ваша заявка сохранена.'
        browser.close()
    assert cli("$p=get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'CONTACT_BROWSER_CHECK']); echo count($p); if ($p) echo str_contains($p[0]->post_content, 'Район: Заокский') ? ':district-saved' : ':missing';") == '1:district-saved'
    print('PASS: desktop/mobile, four contact cards, messenger icons, unique nonce ID, submission and district saved.')
finally:
    cli("foreach(get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'CONTACT_BROWSER_CHECK']) as $p) { if (str_starts_with($p->post_title, 'CONTACT_BROWSER_CHECK —')) wp_delete_post($p->ID, true); }")
