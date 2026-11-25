<?php
if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    register_post_type('strojka_service', [
        'labels' => ['name' => 'Услуги', 'singular_name' => 'Услуга', 'add_new' => 'Добавить услугу', 'add_new_item' => 'Добавить услугу', 'edit_item' => 'Редактировать услугу', 'all_items' => 'Все услуги'],
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-images-alt2',
        'supports' => ['title'], 'delete_with_user' => false,
    ]);
});

add_action('acf/init', function () {
    acf_add_local_field_group([
        'key' => 'group_strojka_service', 'title' => 'Карточка услуги в карусели',
        'fields' => [
            ['key' => 'field_strojka_badge', 'name' => 'service_badge', 'label' => 'Зелёная плашка на фото', 'type' => 'text'],
            ['key' => 'field_strojka_price', 'name' => 'service_price', 'label' => 'Стоимость работ', 'type' => 'text', 'instructions' => 'Например: от 28 000 ₽/м² или По смете.'],
            ['key' => 'field_strojka_duration', 'name' => 'service_duration', 'label' => 'Срок выполнения', 'type' => 'text', 'instructions' => 'Например: от 90 дней или По проекту.'],
            ['key' => 'field_strojka_extra_label', 'name' => 'service_extra_label', 'label' => 'Дополнительная строка: подпись', 'type' => 'text'],
            ['key' => 'field_strojka_extra_value', 'name' => 'service_extra_value', 'label' => 'Дополнительная строка: значение', 'type' => 'text'],
            ['key' => 'field_strojka_tag', 'name' => 'service_tag', 'label' => 'Надпись над заголовком', 'type' => 'text'],
            ['key' => 'field_strojka_image', 'name' => 'service_image', 'label' => 'Изображение', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Если не выбрано, используется иллюстрация дома.'],
            ['key' => 'field_strojka_description', 'name' => 'service_description', 'label' => 'Описание', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
            ['key' => 'field_strojka_features', 'name' => 'service_features', 'label' => 'Список работ', 'type' => 'textarea', 'rows' => 4, 'new_lines' => '', 'instructions' => 'Каждый пункт — с новой строки.'],
            ['key' => 'field_strojka_button', 'name' => 'service_button', 'label' => 'Текст кнопки', 'type' => 'text', 'default_value' => 'Обсудить проект'],
            ['key' => 'field_strojka_link', 'name' => 'service_link', 'label' => 'Ссылка кнопки', 'type' => 'text', 'default_value' => '#request', 'instructions' => 'Адрес страницы или якорь, например #request.'],
            ['key' => 'field_strojka_order', 'name' => 'service_order', 'label' => 'Порядок в карусели', 'type' => 'number', 'default_value' => 0, 'step' => 1, 'instructions' => 'Меньшее число — раньше.'],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'strojka_service']]],
    ]);
});

// Сортировка работает по штатному menu_order и не исключает новые записи без метаполя.
add_action('acf/save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'strojka_service') { return; }
    wp_update_post(['ID' => (int) $post_id, 'menu_order' => (int) get_post_meta($post_id, 'service_order', true)]);
}, 20);

function strojka_services() {
    return get_posts(['post_type' => 'strojka_service', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'ID' => 'ASC']]);
}
