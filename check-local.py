"""Smoke check for the local site; removes its own test lead."""
import re
import subprocess
import urllib.parse
import urllib.request

BASE = 'http://127.0.0.1:8085'

def cli(*args):
    return subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', *args], encoding='utf-8').strip()

with urllib.request.urlopen(BASE) as response:
    html = response.read().decode()
    assert response.status == 200
assert 'strojka/style.css' in html
nonce = re.search(r'name="strojka_nonce" value="([^"]+)', html).group(1)
before = set(cli('post', 'list', '--post_type=strojka_lead', '--post_status=private', '--format=ids').split())
data = urllib.parse.urlencode({'action': 'strojka_request', 'strojka_nonce': nonce, 'client_name': 'LOCAL_FORM_CHECK', 'client_phone': '+7 (999) 123-45-67', 'service': 'Test', 'consent': '1', 'website': ''}).encode()
with urllib.request.urlopen(BASE + '/wp-admin/admin-post.php', data) as response:
    assert 'request=saved' in response.url
    assert 'Ваша заявка сохранена' in response.read().decode()
after = set(cli('post', 'list', '--post_type=strojka_lead', '--post_status=private', '--format=ids').split())
created = after - before
assert len(created) == 1, 'Expected one saved lead'
for post_id in created:
    assert 'LOCAL_FORM_CHECK' in cli('post', 'get', post_id, '--field=post_title')
    cli('post', 'delete', post_id, '--force')
bad_data = urllib.parse.urlencode({'action': 'strojka_request', 'strojka_nonce': nonce, 'client_name': 'Invalid', 'client_phone': 'bad'}).encode()
with urllib.request.urlopen(BASE + '/wp-admin/admin-post.php', bad_data) as response:
    assert 'request=error' in response.url
for path in ['/wp-admin/', '/wp-content/themes/strojka/style.css', '/wp-content/themes/strojka/assets/house.svg']:
    assert urllib.request.urlopen(BASE + path).status == 200
print('PASS: homepage, CSS, SVG, admin, valid submission, database persistence, invalid submission. Test lead removed.')
