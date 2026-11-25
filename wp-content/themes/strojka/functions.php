<?php
if (!defined('ABSPATH')) { exit; }
require_once get_template_directory() . '/inc/services.php';
require_once get_template_directory() . '/inc/facts.php';
require_once get_template_directory() . '/inc/careers.php';
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
});
add_action('customize_register', function ($customizer) {
    $customizer->add_section('strojka_contacts', ['title' => 'Контакты компании']);
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
    $valid = $name !== '' && preg_match('/^[+\d\s()\-]{7,30}$/', $phone) && !empty($_POST['consent']) && empty($_POST['website']);
    $status = 'error';
    if ($valid) {
        $id = wp_insert_post(['post_type' => 'strojka_lead', 'post_status' => 'private', 'post_title' => $name . ' — ' . $phone, 'post_content' => "Имя: $name\nТелефон: $phone\nУслуга: $service\nКомментарий: $message"], true);
        if (!is_wp_error($id) && $id) { $status = 'saved'; }
    }
    $application = isset($_POST['lead_kind']) && sanitize_key(wp_unslash($_POST['lead_kind'])) === 'application';
    wp_safe_redirect(add_query_arg($application ? 'application' : 'request', $status, home_url('/')) . ($application ? '#careers' : '#request'));
    exit;
}
add_action('admin_post_strojka_request', 'strojka_submit_lead');
add_action('admin_post_nopriv_strojka_request', 'strojka_submit_lead');
