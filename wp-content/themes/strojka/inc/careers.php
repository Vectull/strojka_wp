<?php
if (!defined('ABSPATH')) { exit; }
add_action('acf/init', function () {
    $page = get_page_by_path('vakansii');
    if (!$page) { return; }
    acf_add_local_field_group([
        'key' => 'group_strojka_careers', 'title' => 'Баннер вакансий на главной',
        'fields' => [
            ['key' => 'field_careers_kicker', 'name' => 'careers_kicker', 'label' => 'Метка над заголовком', 'type' => 'text', 'default_value' => 'Работа в ТулаСтройГрупп'],
            ['key' => 'field_careers_title', 'name' => 'careers_title', 'label' => 'Заголовок баннера', 'type' => 'text', 'default_value' => 'Ищем мастеров в строительный трест Тулы'],
            ['key' => 'field_careers_description', 'name' => 'careers_description', 'label' => 'Текст баннера', 'type' => 'textarea', 'rows' => 4, 'new_lines' => '', 'default_value' => 'Открыт набор на объекты в г. Тула и области: прорабы, кровельщики, монолитчики-бетонщики, арбористы, монтажники деревянных конструкций. Белая оплата каждую неделю, профессиональный инструмент Hilti/Makita, спецодежда и доставка до объекта.'],
            ['key' => 'field_careers_tags', 'name' => 'careers_tags', 'label' => 'Условия работы в плашках', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '', 'instructions' => 'Каждая плашка — с новой строки.', 'default_value' => "З/П от 85 000 до 160 000 ₽\nОплата без задержек\nИногородним жильё"],
        ], 'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $page->ID]]],
    ]);
});
