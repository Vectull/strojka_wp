<?php
if (!defined('ABSPATH')) { exit; }
add_action('init', function () {
    register_post_type('strojka_fact', ['labels' => ['name' => 'Показатели компании', 'singular_name' => 'Показатель', 'add_new' => 'Добавить показатель', 'add_new_item' => 'Добавить показатель', 'edit_item' => 'Редактировать показатель'], 'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-chart-bar', 'supports' => ['title'], 'delete_with_user' => false]);
});
add_action('acf/init', function () {
    acf_add_local_field_group([
        'key' => 'group_strojka_facts', 'title' => 'Карточка показателя над каруселью',
        'fields' => [
            ['key' => 'field_strojka_fact_value', 'name' => 'fact_value', 'label' => 'Крупный показатель', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '', 'instructions' => 'Например: 420+ или ГОСТ / СНиП. Перенос строки сохраняется.'],
            ['key' => 'field_strojka_fact_subtitle', 'name' => 'fact_subtitle', 'label' => 'Подзаголовок', 'type' => 'text'],
            ['key' => 'field_strojka_fact_description', 'name' => 'fact_description', 'label' => 'Описание', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
            ['key' => 'field_strojka_fact_icon', 'name' => 'fact_icon', 'label' => 'Значок', 'type' => 'select', 'choices' => ['building' => 'Здание', 'machine' => 'Техника', 'shield' => 'Щит', 'checklist' => 'Проверка'], 'default_value' => 'building'],
            ['key' => 'field_strojka_fact_serif', 'name' => 'fact_serif', 'label' => 'Шрифт показателя с засечками', 'type' => 'true_false', 'ui' => 1],
            ['key' => 'field_strojka_fact_order', 'name' => 'fact_order', 'label' => 'Порядок карточки', 'type' => 'number', 'default_value' => 0, 'step' => 1],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'strojka_fact']]],
    ]);
});
add_action('acf/save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'strojka_fact') { return; }
    wp_update_post(['ID' => (int) $post_id, 'menu_order' => (int) get_post_meta($post_id, 'fact_order', true)]);
}, 20);
