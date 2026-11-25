import subprocess
from playwright.sync_api import sync_playwright

def cli(code):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip()

try:
    url = cli("echo get_permalink(get_page_by_path('vakansii'));")
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width':1440, 'height':1000})
        errors = []
        page.on('pageerror', lambda error: errors.append(str(error)))
        page.goto(url, wait_until='networkidle')
        assert page.locator('.job-card').count() == 5
        page.screenshot(path='.local-tools/jobs-desktop.png', full_page=True)
        page.locator('[data-job]').first.click()
        assert 'Прорабы' in page.locator('.jobs-application textarea').input_value()
        page.set_viewport_size({'width':390,'height':844})
        assert not page.evaluate('document.documentElement.scrollWidth > innerWidth')
        page.screenshot(path='.local-tools/jobs-mobile.png', full_page=True)
        form = page.locator('.jobs-application')
        form.locator('[name="client_name"]').fill('JOBS_PAGE_CHECK')
        form.locator('[name="client_phone"]').fill('+7 (999) 123-45-67')
        form.locator('[name="consent"]').check()
        form.get_by_role('button', name='Отправить отклик').click()
        page.wait_for_url('**application=saved#apply')
        assert form.get_by_role('status').inner_text() == 'Спасибо! Ваш отклик сохранён.'
        assert page.locator('.jobs-page').count() == 1
        assert not errors, errors
        browser.close()
    assert cli("$p=get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'JOBS_PAGE_CHECK']); echo count($p); if ($p) echo str_contains($p[0]->post_content, 'Прорабы') ? ':specialty-saved' : ':missing';") == '1:specialty-saved'
    print('PASS: five job cards, desktop/mobile, specialty prefill, submission saved and returned to vacancies page.')
finally:
    cli("foreach(get_posts(['post_type'=>'strojka_lead','post_status'=>'private','s'=>'JOBS_PAGE_CHECK']) as $p) { if (str_starts_with($p->post_title, 'JOBS_PAGE_CHECK —')) wp_delete_post($p->ID, true); }")
