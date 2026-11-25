<?php
if (!defined('ABSPATH')) { exit; }

function strojka_external_seo() {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION') || defined('SEOPRESS_VERSION');
}
function strojka_seo_data() {
    $brand = get_theme_mod('strojka_brand', 'ТУЛАСТРОЙГРУПП');
    $id = get_queried_object_id();
    $url = is_front_page() ? home_url('/') : (is_singular() ? get_permalink($id) : home_url('/'));
    $title = is_front_page() ? get_theme_mod('strojka_seo_title', 'Строительство домов в Туле и области — ' . $brand) : get_the_title($id) . ' — ' . $brand;
    $description = is_front_page() ? get_theme_mod('strojka_seo_description', 'Строительство домов, фундаменты и благоустройство в Туле и Тульской области. Согласование сметы, поэтапные работы и выезд инженера на участок.') : '';
    if (is_page('vakansii')) { $description = 'Работа в строительстве в Туле и области: прорабы, кровельщики, бетонщики и монтажники. Условия работы и отклик на вакансию в ' . $brand . '.'; }
    if (is_singular()) {
        $title = get_post_meta($id, 'seo_title', true) ?: $title;
        $description = get_post_meta($id, 'seo_description', true) ?: $description;
        if (!$description) {
            $source = get_post_type($id) === 'strojka_service' ? get_post_meta($id, 'service_description', true) : get_post_field('post_content', $id);
            $description = wp_trim_words(wp_strip_all_tags(strip_shortcodes($source)), 28, '…');
        }
    }
    if (is_404()) { $title = 'Страница не найдена — ' . $brand; }
    if (is_search()) { $title = 'Поиск по сайту — ' . $brand; }
    $image_id = is_singular() ? (int) get_post_meta($id, 'seo_image', true) : 0;
    if (!$image_id && is_singular('strojka_service')) { $image_id = (int) get_post_meta($id, 'service_image', true); }
    $image = $image_id ? wp_get_attachment_image_url($image_id, 'large') : false;
    return ['title' => wp_strip_all_tags($title), 'description' => wp_strip_all_tags($description), 'url' => $url, 'image' => $image ?: get_template_directory_uri() . '/assets/service-house.jpg', 'brand' => $brand];
}
function strojka_seo_noindex() {
    return !get_option('blog_public') || is_search() || is_404() || is_archive() || is_attachment()
        || (is_singular() && get_post_meta(get_queried_object_id(), 'seo_noindex', true))
        || isset($_GET['request']) || isset($_GET['application']) || isset($_GET['quick']);
}
add_action('acf/init', function () {
    acf_add_local_field_group(['key' => 'group_strojka_seo', 'title' => 'SEO страницы', 'fields' => [
        ['key' => 'field_strojka_seo_title', 'name' => 'seo_title', 'label' => 'Заголовок в поиске (Title)', 'type' => 'text', 'instructions' => 'Уникальный заголовок. Если пусто — формируется автоматически.'],
        ['key' => 'field_strojka_seo_description', 'name' => 'seo_description', 'label' => 'Описание в поиске (Description)', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
        ['key' => 'field_strojka_seo_image', 'name' => 'seo_image', 'label' => 'Изображение при отправке ссылки', 'type' => 'image', 'return_format' => 'id'],
        ['key' => 'field_strojka_seo_noindex', 'name' => 'seo_noindex', 'label' => 'Скрыть страницу от поисковиков и из sitemap', 'type' => 'true_false', 'ui' => 1],
    ], 'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'page']], [['param' => 'post_type', 'operator' => '==', 'value' => 'strojka_service']]]]);
});
add_action('customize_register', function ($customizer) {
    $customizer->add_section('strojka_seo', ['title' => 'SEO и поисковые системы']);
    foreach (['title' => ['Title главной', 'Строительство домов в Туле и области — ТУЛАСТРОЙГРУПП'], 'description' => ['Description главной', 'Строительство домов, фундаменты и благоустройство в Туле и Тульской области. Согласование сметы, поэтапные работы и выезд инженера на участок.'], 'yandex_verify' => ['Код подтверждения Яндекс Вебмастера', ''], 'google_verify' => ['Код подтверждения Google Search Console', '']] as $key => $item) {
        $customizer->add_setting('strojka_seo_' . $key, ['default' => $item[1], 'sanitize_callback' => 'sanitize_text_field']);
        $customizer->add_control('strojka_seo_' . $key, ['label' => $item[0], 'section' => 'strojka_seo', 'type' => $key === 'description' ? 'textarea' : 'text']);
    }
    foreach (['telegram' => 'Ссылка Telegram', 'max' => 'Ссылка MAX'] as $key => $label) {
        $customizer->add_setting('strojka_' . $key, ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
        $customizer->add_control('strojka_' . $key, ['label' => $label, 'section' => 'strojka_contacts', 'type' => 'url']);
    }
});
add_action('after_setup_theme', function () {
    if (strojka_external_seo()) { return; }
    remove_action('wp_head', 'rel_canonical');
    add_filter('pre_get_document_title', function () { return strojka_seo_data()['title']; });
    add_filter('wp_robots', function ($robots) {
        if (strojka_seo_noindex()) { unset($robots['index']); $robots['noindex'] = true; }
        $robots['max-image-preview'] = 'large';
        return $robots;
    });
    add_action('wp_head', function () {
        $seo = strojka_seo_data();
        echo '<meta name="description" content="' . esc_attr($seo['description']) . '">' . "\n";
        if (!is_404() && !is_search() && !is_archive()) { echo '<link rel="canonical" href="' . esc_url($seo['url']) . '">' . "\n"; }
        foreach (['og:type' => 'website', 'og:locale' => 'ru_RU', 'og:site_name' => $seo['brand'], 'og:title' => $seo['title'], 'og:description' => $seo['description'], 'og:url' => $seo['url'], 'og:image' => $seo['image']] as $key => $value) {
            echo '<meta property="' . esc_attr($key) . '" content="' . esc_attr($value) . '">' . "\n";
        }
        foreach (['twitter:card' => 'summary_large_image', 'twitter:title' => $seo['title'], 'twitter:description' => $seo['description'], 'twitter:image' => $seo['image']] as $key => $value) { echo '<meta name="' . esc_attr($key) . '" content="' . esc_attr($value) . '">' . "\n"; }
        foreach (['yandex' => 'yandex-verification', 'google' => 'google-site-verification'] as $key => $name) {
            $value = get_theme_mod('strojka_seo_' . $key . '_verify', '');
            if ($value) { echo '<meta name="' . esc_attr($name) . '" content="' . esc_attr($value) . '">' . "\n"; }
        }
        if (is_404() || is_search() || is_archive()) { return; }
        $home = home_url('/');
        $business = ['@type' => 'GeneralContractor', '@id' => $home . '#company', 'name' => $seo['brand'], 'url' => $home, 'image' => get_template_directory_uri() . '/assets/service-house.jpg', 'telephone' => get_theme_mod('strojka_phone', '+7 (4872) 75-30-20'), 'address' => ['@type' => 'PostalAddress', 'streetAddress' => get_theme_mod('strojka_address', 'г. Тула, ул. Советская 47'), 'addressLocality' => 'Тула', 'addressCountry' => 'RU'], 'areaServed' => 'Тула и Тульская область'];
        $graph = [$business, ['@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => $seo['brand'], 'inLanguage' => 'ru-RU'], ['@type' => 'WebPage', '@id' => $seo['url'] . '#webpage', 'url' => $seo['url'], 'name' => $seo['title'], 'description' => $seo['description'], 'isPartOf' => ['@id' => $home . '#website'], 'inLanguage' => 'ru-RU']];
        if (!is_front_page()) {
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $home], ['@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => $seo['url']]]];
        }
        if (is_singular('strojka_service')) { $graph[] = ['@type' => 'Service', 'name' => get_the_title(), 'description' => $seo['description'], 'url' => $seo['url'], 'provider' => ['@id' => $home . '#company'], 'areaServed' => 'Тула и Тульская область']; }
        echo '<script type="application/ld+json">' . wp_json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>' . "\n";
    }, 3);
    add_filter('wp_sitemaps_post_types', function ($types) { return array_intersect_key($types, array_flip(['page', 'strojka_service'])); });
    add_filter('wp_sitemaps_taxonomies', '__return_empty_array');
    add_filter('wp_sitemaps_add_provider', function ($provider, $name) { return $name === 'users' ? false : $provider; }, 10, 2);
    add_filter('wp_sitemaps_posts_query_args', function ($args) {
        $args['meta_query'] = ['relation' => 'OR', ['key' => 'seo_noindex', 'compare' => 'NOT EXISTS'], ['key' => 'seo_noindex', 'value' => '1', 'compare' => '!=']];
        return $args;
    });
    add_filter('robots_txt', function ($output, $public) {
        if (!$public) { return "User-agent: *\nDisallow: /\n"; }
        return $output;
    }, 10, 2);
});
