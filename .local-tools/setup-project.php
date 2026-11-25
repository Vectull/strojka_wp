<?php
// After installing WordPress, activating ACF and the strojka theme:
// php wp-cli.phar eval-file .local-tools/setup-project.php
if (!function_exists('update_field') || !post_type_exists('strojka_service')) {
    throw new Exception('Activate Advanced Custom Fields and the strojka theme first.');
}
require_once ABSPATH . 'wp-admin/includes/image.php';
$data = json_decode(file_get_contents(__DIR__ . '/project-content.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($data['posts'] as $item) {
    $existing = get_posts(['post_type' => $item['type'], 'post_status' => 'any', 'meta_key' => '_strojka_source_key', 'meta_value' => $item['source_key'], 'numberposts' => 1]);
    if ($existing) { continue; }
    // Preserve existing pages if this bootstrap is run against an installed site.
    if ($item['type'] === 'page' && get_page_by_path($item['slug'])) { continue; }
    $id = wp_insert_post(['post_type' => $item['type'], 'post_status' => 'publish', 'post_name' => $item['slug'], 'post_title' => $item['title'], 'post_content' => str_replace('{{SITE_URL}}', home_url(), $item['content']), 'menu_order' => $item['order']], true);
    if (is_wp_error($id)) { throw new Exception($id->get_error_message()); }
    update_post_meta($id, '_strojka_source_key', $item['source_key']);
    foreach ($item['meta'] as $key => $value) {
        $field = str_starts_with($key, 'service_') ? 'field_strojka_' . substr($key, 8) : ((str_starts_with($key, 'fact_') || str_starts_with($key, 'seo_')) ? 'field_strojka_' . $key : 'field_' . $key);
        update_field($field, $value, $id);
    }
    if (!empty($item['image_asset'])) {
        $asset = basename($item['image_asset']);
        $upload = wp_upload_bits($asset, null, file_get_contents(get_template_directory() . '/assets/' . $asset));
        if ($upload['error']) { throw new Exception($upload['error']); }
        $image = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => $item['title'], 'post_status' => 'inherit'], $upload['file'], 0, true);
        if (is_wp_error($image)) { throw new Exception($image->get_error_message()); }
        wp_update_attachment_metadata($image, wp_generate_attachment_metadata($image, $upload['file']));
        update_post_meta($image, '_wp_attachment_image_alt', $item['title']);
        update_field('field_strojka_image', $image, $id);
    }
}
foreach ($data['theme_mods'] as $key => $value) {
    if (get_theme_mod($key, null) === null) { set_theme_mod($key, $value); }
}
if (!has_nav_menu('primary')) {
    $menu = wp_get_nav_menu_object('Основное меню');
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Основное меню');
    if (is_wp_error($menu_id)) { throw new Exception($menu_id->get_error_message()); }
    foreach ($data['menu'] as $item) {
        wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $item['title'], 'menu-item-url' => str_replace('{{SITE_URL}}', home_url(), $item['url']), 'menu-item-status' => 'publish']);
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
update_option('strojka_facts_seeded', 1);
update_option('strojka_services_migrated', 1);
update_option('permalink_structure', '/%postname%/');
update_option('WPLANG', 'ru_RU');
flush_rewrite_rules(false);
echo "Project content restored.\n";
