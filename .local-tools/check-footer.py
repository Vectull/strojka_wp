import subprocess
from playwright.sync_api import sync_playwright

def cli(code):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip()

try:
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width':1440,'height':1000})
        page.goto('http://127.0.0.1:8085/',wait_until='domcontentloaded')
        footer = page.locator('.site-footer')
        footer.screenshot(path='.local-tools/footer-desktop.png')
        assert page.locator('#footer_nonce').count() == 1
        page.set_viewport_size({'width':390,'height':844})
        footer.screenshot(path='.local-tools/footer-mobile.png')
        assert not page.evaluate('document.documentElement.scrollWidth > innerWidth')
        footer.locator('[name="client_name"]').evaluate("el => el.value = 'FOOTER_BROWSER_CHECK'")
        footer.locator('[name="client_phone"]').fill('+7 (999) 123-45-67')
        footer.locator('[name="consent"]').check()
        footer.get_by_role('button',name='Отправить',exact=True).click()
        page.wait_for_url('**/?quick=saved#footer')
        assert footer.get_by_role('status').inner_text() == 'Спасибо! Заявка на звонок сохранена.'
        browser.close()
    assert cli("$p=get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'FOOTER_BROWSER_CHECK']); echo count($p); if ($p) echo str_contains($p[0]->post_content, 'Заявка на звонок из футера') ? ':saved' : ':missing';") == '1:saved'
    print('PASS: desktop/mobile footer, unique nonce, callback submitted and saved.')
finally:
    cli("foreach(get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'FOOTER_BROWSER_CHECK']) as $p) { if (str_starts_with($p->post_title, 'FOOTER_BROWSER_CHECK —')) wp_delete_post($p->ID, true); }")
