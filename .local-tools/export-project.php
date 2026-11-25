<?php
// Run with WP-CLI. Only public site content; no users, leads, credentials or DB dump.
$result = ['posts' => [], 'theme_mods' => [], 'menu' => []];
$posts = get_posts(['post_type' => ['page', 'strojka_service', 'strojka_fact'], 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
foreach ($posts as $post) {
    if ($post->post_type === 'page' && $post->post_name === 'sample-page') { continue; }
    $item = ['source_key' => 'strojka-' . $post->ID, 'type' => $post->post_type, 'slug' => $post->post_name, 'title' => $post->post_title, 'content' => str_replace(home_url(), '{{SITE_URL}}', $post->post_content), 'order' => $post->menu_order, 'meta' => []];
    foreach (get_post_meta($post->ID) as $key => $values) {
        if (preg_match('/^(service_|fact_|careers_|seo_)/', $key) && $key !== 'service_image' && $key !== 'seo_image') {
            $item['meta'][$key] = get_post_meta($post->ID, $key, true);
        }
    }
    $image_id = (int) get_post_meta($post->ID, 'service_image', true);
    if ($image_id) {
        $asset = basename(get_attached_file($image_id));
        if (is_file(get_template_directory() . '/assets/' . $asset)) { $item['image_asset'] = $asset; }
    }
    $result['posts'][] = $item;
}
foreach (get_theme_mods() as $key => $value) {
    if (str_starts_with($key, 'strojka_') && $key !== 'strojka_yandex_maps_key') { $result['theme_mods'][$key] = $value; }
}
$locations = get_nav_menu_locations();
foreach (wp_get_nav_menu_items($locations['primary'] ?? 0) ?: [] as $item) {
    $result['menu'][] = ['title' => $item->title, 'url' => str_replace(home_url(), '{{SITE_URL}}', $item->url)];
}
file_put_contents(__DIR__ . '/project-content.json', wp_json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n");
echo "Public project content exported.\n";
