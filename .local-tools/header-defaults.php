<?php
foreach (['phone' => '+7 (4872) 75-30-20', 'region' => 'Тула и Тульская область'] as $key => $value) {
    if (get_theme_mod('strojka_' . $key, null) === null) { set_theme_mod('strojka_' . $key, $value); }
}
