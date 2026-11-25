<?php
if (!defined('ABSPATH')) { exit; }
require_once get_template_directory() . '/inc/services.php';
require_once get_template_directory() . '/inc/facts.php';
require_once get_template_directory() . '/inc/careers.php';
require_once get_template_directory() . '/inc/seo.php';
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => 'Основное меню']);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('strojka', get_stylesheet_uri(), [], filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_style('strojka-facts', get_template_directory_uri() . '/assets/facts.css', ['strojka'], filemtime(get_template_directory() . '/assets/facts.css'));
    wp_enqueue_style('strojka-carousel', get_template_directory_uri() . '/assets/carousel.css', ['strojka'], filemtime(get_template_directory() . '/assets/carousel.css'));
    wp_enqueue_script('strojka-carousel', get_template_directory_uri() . '/assets/carousel.js', [], filemtime(get_template_directory() . '/assets/carousel.js'), true);
    wp_enqueue_style('strojka-header', get_template_directory_uri() . '/assets/header.css', ['strojka-carousel'], filemtime(get_template_directory() . '/assets/header.css'));
    wp_enqueue_style('strojka-careers', get_template_directory_uri() . '/assets/careers.css', ['strojka'], filemtime(get_template_directory() . '/assets/careers.css'));
    wp_enqueue_script('strojka-careers', get_template_directory_uri() . '/assets/careers.js', [], filemtime(get_template_directory() . '/assets/careers.js'), true);
    wp_enqueue_style('strojka-contact', get_template_directory_uri() . '/assets/contact.css', ['strojka'], filemtime(get_template_directory() . '/assets/contact.css'));
    if (is_front_page()) {
        wp_enqueue_style('strojka-yandex-map', get_template_directory_uri() . '/assets/yandex-map.css', ['strojka-contact'], filemtime(get_template_directory() . '/assets/yandex-map.css'));
        if (trim(get_theme_mod('strojka_yandex_maps_key', '')) !== '') {
            wp_enqueue_script('strojka-yandex-map', get_template_directory_uri() . '/assets/yandex-map.js', [], filemtime(get_template_directory() . '/assets/yandex-map.js'), true);
        }
    }
    wp_enqueue_style('strojka-footer', get_template_directory_uri() . '/assets/footer.css', ['strojka'], filemtime(get_template_directory() . '/assets/footer.css'));
    wp_enqueue_style('strojka-seo', get_template_directory_uri() . '/assets/seo.css', ['strojka-contact'], filemtime(get_template_directory() . '/assets/seo.css'));
    if (is_page('vakansii')) {
        wp_enqueue_style('strojka-jobs', get_template_directory_uri() . '/assets/jobs.css', ['strojka-contact'], filemtime(get_template_directory() . '/assets/jobs.css'));
    }
});
add_action('customize_register', function ($customizer) {
    foreach (['office_note' => ['Уточнение адреса офиса', 'БЦ «Европейский», офис 304'], 'warehouse' => ['Адрес базы спецтехники', 'г. Тула, Новомосковское шоссе, 12'], 'warehouse_note' => ['Описание базы', 'Склад бетона, пиломатериалов и ангар техники'], 'emergency' => ['Дополнительный режим работы', 'Аварийные арбористы: круглосуточно']] as $key => $item) {
        $customizer->add_setting('strojka_' . $key, ['default' => $item[1], 'sanitize_callback' => 'sanitize_text_field']);
        $customizer->add_control('strojka_' . $key, ['label' => $item[0], 'section' => 'strojka_contacts']);
    }
    $customizer->add_section('strojka_contacts', ['title' => 'Контакты компании']);
    $customizer->add_setting('strojka_yandex_maps_key', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $customizer->add_control('strojka_yandex_maps_key', ['label' => 'API-ключ Яндекс Карт', 'description' => 'Ключ JavaScript API. Без ключа отображается виджет Яндекс Карт. Адрес берётся из поля «Адрес».', 'section' => 'strojka_contacts']);
    $customizer->add_setting('strojka_footer_description', ['default' => 'Генеральный подрядчик полного цикла. Инженерное проектирование и капитальное строительство объектов в Туле, Новомосковске, Алексине, Щёкино и на всей территории Тульской области.', 'sanitize_callback' => 'sanitize_textarea_field']);
    $customizer->add_control('strojka_footer_description', ['label' => 'Описание компании в футере', 'type' => 'textarea', 'section' => 'strojka_contacts']);
    foreach (['phone' => ['Телефон', '+7 (4872) 75-30-20'], 'region' => ['Регион работы', 'Тула и Тульская область'], 'brand' => ['Название в шапке', 'ТУЛАСТРОЙГРУПП'], 'tagline' => ['Подпись под логотипом', 'Строительный трест Тулы и ТО'], 'address' => ['Адрес', 'г. Тула, ул. Советская 47'], 'hours' => ['Режим работы', 'Ежедневно 8:00 – 21:00'], 'phone_note' => ['Подпись под телефоном', 'Прямая линия инженеров'], 'availability' => ['Статус в шапке', 'Дежурный инженер на связи'], 'season' => ['Метка строительного сезона', 'Строительный сезон · Тула и ТО'], 'sro' => ['Строка СРО (пусто — скрыть)', '']] as $key => $item) {
        $customizer->add_setting('strojka_' . $key, ['default' => $item[1], 'sanitize_callback' => 'sanitize_text_field']);
        $customizer->add_control('strojka_' . $key, ['label' => $item[0], 'section' => 'strojka_contacts']);
    }
});
// Заявки сохраняются локально и доступны администратору даже без почтового сервера.
add_action('init', function () {
    register_post_type('strojka_lead', ['labels' => ['name' => 'Заявки', 'singular_name' => 'Заявка'], 'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-email-alt', 'supports' => ['title', 'editor'], 'capabilities' => ['create_posts' => 'do_not_allow'], 'map_meta_cap' => true]);
});
function strojka_submit_lead() {
    if (!isset($_POST['strojka_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['strojka_nonce'])), 'strojka_request')) {
        wp_die('Обновите страницу и попробуйте ещё раз.', '', ['response' => 403]);
    }
    $name = sanitize_text_field(wp_unslash($_POST['client_name'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['client_phone'] ?? ''));
    $service = sanitize_text_field(wp_unslash($_POST['service'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    $district = sanitize_text_field(wp_unslash($_POST['district'] ?? ''));
    $valid = $name !== '' && preg_match('/^[+\d\s()\-]{7,30}$/', $phone) && !empty($_POST['consent']) && empty($_POST['website']);
    $status = 'error';
    if ($valid) {
        $id = wp_insert_post(['post_type' => 'strojka_lead', 'post_status' => 'private', 'post_title' => $name . ' — ' . $phone, 'post_content' => "Имя: $name\nТелефон: $phone\nУслуга: $service\nРайон: $district\nКомментарий: $message"], true);
        if (!is_wp_error($id) && $id) { $status = 'saved'; }
    }
    $kind = sanitize_key(wp_unslash($_POST['lead_kind'] ?? ''));
    $application = in_array($kind, ['application', 'application_page'], true);
    $destination = home_url('/');
    $anchor = $application ? '#careers' : '#request';
    if ($kind === 'footer') { $anchor = '#footer'; }
    if ($kind === 'application_page' && ($page = get_page_by_path('vakansii'))) {
        $destination = get_permalink($page);
        $anchor = '#apply';
    }
    wp_safe_redirect(add_query_arg($kind === 'footer' ? 'quick' : ($application ? 'application' : 'request'), $status, $destination) . $anchor);
    exit;
}
add_action('admin_post_strojka_request', 'strojka_submit_lead');
add_action('admin_post_nopriv_strojka_request', 'strojka_submit_lead');
