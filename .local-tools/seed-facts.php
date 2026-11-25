<?php
if (get_option('strojka_facts_seeded')) { return; }
$cards = [
    ['География треста', '420+', 'Сданных объектов', 'Заокский, Ясногорск, Алексин, Щёкино, Венёв, Новомосковск.', 'building', 0],
    ['Машинный фонд', '18 ЕД.', 'Своя спецтехника', 'Экскаваторы, виброкатки, автовышки 22 м, самосвалы и песковозы.', 'machine', 1],
    ['Финансовая броня', '100%', 'Фиксация сметы', 'Никаких скрытых расходов, подорожания стройматериалов и наценок в процессе.', 'shield', 0],
    ['Стандарты РФ', "ГОСТ /\nСНиП", 'Строгий технадзор', 'Лабораторный контроль бетона, акты скрытых работ на всех циклах.', 'checklist', 1],
];
foreach ($cards as $index => $card) {
    $id = wp_insert_post(['post_type' => 'strojka_fact', 'post_status' => 'publish', 'post_title' => $card[0], 'menu_order' => $index], true);
    if (is_wp_error($id)) { throw new Exception($id->get_error_message()); }
    foreach (['value' => $card[1], 'subtitle' => $card[2], 'description' => $card[3], 'icon' => $card[4], 'serif' => $card[5], 'order' => $index] as $key => $value) {
        update_field('field_strojka_fact_' . $key, $value, $id);
    }
}
update_option('strojka_facts_seeded', 1);
echo "Four fact cards created.\n";
