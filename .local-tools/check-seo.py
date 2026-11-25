import json
import subprocess
import urllib.error
import urllib.request
from html.parser import HTMLParser

BASE = 'http://127.0.0.1:8085'
class Head(HTMLParser):
    def __init__(self):
        super().__init__()
        self.meta = {}
        self.canonical = []
        self.schema = []
        self.titles = 0
        self.h1 = 0
        self.in_schema = False
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'meta': self.meta[a.get('name', a.get('property', ''))] = a.get('content', '')
        if tag == 'link' and a.get('rel') == 'canonical': self.canonical.append(a['href'])
        if tag == 'title': self.titles += 1
        if tag == 'h1': self.h1 += 1
        if tag == 'script' and a.get('type') == 'application/ld+json': self.in_schema = True
    def handle_endtag(self, tag):
        if tag == 'script': self.in_schema = False
    def handle_data(self, data):
        if self.in_schema: self.schema.append(json.loads(data))

for path in ['/', '/vakansii/', '/uslugi/stroitelstvo-domov/', '/uslugi/fundamenty-i-zemlyanye-raboty/', '/uslugi/blagoustroystvo-uchastka/']:
    response = urllib.request.urlopen(BASE + path)
    html = response.read().decode()
    head = Head(); head.feed(html)
    assert response.status == 200, path
    assert head.titles == 1 and head.h1 == 1, (path, head.titles, head.h1)
    assert len(head.canonical) == 1 and head.canonical[0] == BASE + path, (path, head.canonical)
    assert head.meta['description'] and head.meta['og:image'] and head.meta['twitter:card']
    assert 'noindex' in head.meta['robots'], 'Local indexing must remain disabled'
    assert 'lang="ru-RU"' in html or 'lang="ru"' in html
    assert head.schema[0]['@context'] == 'https://schema.org'
    if path.startswith('/uslugi/'):
        assert any(item['@type'] == 'Service' for item in head.schema[0]['@graph'])
    print('PASS metadata, canonical, headings, schema:', path)
head = Head(); head.feed(urllib.request.urlopen(BASE + '/?request=saved').read().decode())
assert head.canonical == [BASE + '/']
assert 'noindex' in head.meta['robots']
assert 'Disallow: /' in urllib.request.urlopen(BASE + '/robots.txt').read().decode()
try:
    urllib.request.urlopen(BASE + '/not-a-real-page-check/')
    raise AssertionError('Missing 404 status')
except urllib.error.HTTPError as error:
    assert error.code == 404
    head = Head(); head.feed(error.read().decode())
    assert 'noindex' in head.meta['robots']

code = r'''
add_filter('option_blog_public', '__return_true');
$server = wp_sitemaps_get_server();
$server->register_sitemaps();
if (!$server->sitemaps_enabled()) { throw new Exception('Production sitemap disabled'); }
$provider = $server->registry->get_provider('posts');
$urls = $provider->get_url_list(1, 'strojka_service');
if (count($urls) !== 3) { throw new Exception('Expected three services in sitemap'); }
if ($server->registry->get_provider('users')) { throw new Exception('Authors must not be exposed'); }
$id = wp_insert_post(['post_type'=>'strojka_service','post_status'=>'publish','post_title'=>'SEO_NOINDEX_TEST']);
try {
    update_post_meta($id, 'seo_noindex', '1');
    foreach ($provider->get_url_list(1, 'strojka_service') as $url) {
        if ($url['loc'] === get_permalink($id)) { throw new Exception('Noindex page leaked into sitemap'); }
    }
} finally { wp_delete_post($id, true); }
echo 'PASS production sitemap: services included; authors and noindex excluded; local setting unchanged.';
'''
print(subprocess.check_output(['C:/xampp/php/php.exe', '-d', 'display_errors=0', 'wp-cli.phar', 'eval', code], encoding='utf-8').strip())
