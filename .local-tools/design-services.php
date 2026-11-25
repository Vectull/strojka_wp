<?php
require_once ABSPATH . 'wp-admin/includes/image.php';
$assets = ['service-house.jpg', 'service-build.jpg', 'service-garden.jpg'];
$badges = ['Смета до начала работ', 'Надёжное основание', 'Комплексное решение'];
foreach (strojka_services() as $index => $service) {
    if ($index >= count($assets)) { break; }
    if (!get_post_meta($service->ID, 'service_image', true)) {
        $asset = $assets[$index];
        $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_strojka_demo_asset', 'meta_value' => $asset, 'numberposts' => 1]);
        if ($existing) {
            $attachment_id = $existing[0]->ID;
        } else {
            $upload = wp_upload_bits($asset, null, file_get_contents(get_template_directory() . '/assets/' . $asset));
            if ($upload['error']) { throw new Exception($upload['error']); }
            $attachment_id = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => get_the_title($service->ID) . ' — демонстрационное фото', 'post_status' => 'inherit'], $upload['file'], 0, true);
            if (is_wp_error($attachment_id)) { throw new Exception($attachment_id->get_error_message()); }
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
            update_post_meta($attachment_id, '_strojka_demo_asset', $asset);
            update_post_meta($attachment_id, '_wp_attachment_image_alt', get_the_title($service->ID));
        }
        update_field('field_strojka_image', $attachment_id, $service->ID);
    }
    if (!metadata_exists('post', $service->ID, 'service_badge')) {
        update_field('field_strojka_badge', $badges[$index], $service->ID);
        update_field('field_strojka_extra_label', 'Условия работ', $service->ID);
        update_field('field_strojka_extra_value', 'По договору', $service->ID);
    }
}
echo "Catalog images and fields ready.\n";
