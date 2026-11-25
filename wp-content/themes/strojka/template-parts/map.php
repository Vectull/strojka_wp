<?php
$address = get_theme_mod('strojka_address', 'г. Тула, ул. Советская 47');
$key = trim(get_theme_mod('strojka_yandex_maps_key', ''));
$widget = add_query_arg(['text' => $address, 'z' => 15], 'https://yandex.ru/map-widget/v1/');
$link = add_query_arg(['text' => $address], 'https://yandex.ru/maps/');
?>
<div class="contact-map yandex-contact-map">
<?php if ($key): ?><div class="yandex-map-canvas" data-yandex-map data-key="<?php echo esc_attr($key); ?>" data-address="<?php echo esc_attr($address); ?>" data-widget="<?php echo esc_url($widget); ?>" role="region" aria-label="Адрес офиса на Яндекс Картах"><p>Карта загружается…</p></div><noscript><iframe title="Офис на Яндекс Картах" src="<?php echo esc_url($widget); ?>" loading="lazy" allowfullscreen></iframe></noscript><?php else: ?><iframe title="Офис на Яндекс Картах" src="<?php echo esc_url($widget); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe><?php endif; ?>
<a class="yandex-map-link" href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer">Открыть в Яндекс Картах</a></div>
