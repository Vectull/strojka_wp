<?php
$settings = [
    'Дома для жизни' => ['stroitelstvo-domov', 'Строительство домов под ключ в Туле — ТУЛАСТРОЙГРУПП', 'Строительство домов в Туле и области: коробка, кровля и поэтапная сдача работ. Подбор решения под участок, согласование сметы и консультация инженера.'],
    'Фундаменты и земляные работы' => ['fundamenty-i-zemlyanye-raboty', 'Фундаменты и земляные работы в Туле — ТУЛАСТРОЙГРУПП', 'Подготовка участка, разработка котлована и устройство фундамента в Туле и области. Обсудим условия участка, объём работ и смету.'],
    'Благоустройство участка' => ['blagoustroystvo-uchastka', 'Благоустройство участка в Туле — ТУЛАСТРОЙГРУПП', 'Благоустройство участков в Туле и области: мощение дорожек, дренаж, водоотведение и обустройство территории. Консультация и расчёт сметы.'],
];
foreach (strojka_services() as $service) {
    if (!isset($settings[$service->post_title])) { continue; }
    [$slug, $title, $description] = $settings[$service->post_title];
    if (str_contains($service->post_name, '%')) { wp_update_post(['ID' => $service->ID, 'post_name' => $slug]); }
    if (!get_post_meta($service->ID, 'seo_title', true)) { update_field('field_strojka_seo_title', $title, $service->ID); }
    if (!get_post_meta($service->ID, 'seo_description', true)) { update_field('field_strojka_seo_description', $description, $service->ID); }
}
foreach (['sample-page' => 'Sample Page', 'hello-world' => 'Hello world!'] as $slug => $title) {
    $post = get_page_by_path($slug, OBJECT, ['page', 'post']);
    if ($post && $post->post_title === $title) { wp_update_post(['ID' => $post->ID, 'post_status' => 'draft']); }
}
update_option('permalink_structure', '/%postname%/');
update_option('WPLANG', 'ru_RU');
flush_rewrite_rules(false);
echo "SEO URLs and metadata prepared; indexing setting unchanged.\n";
