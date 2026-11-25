<?php $services = strojka_services(); if (!$services) { return; } ?>
<div class="services-carousel" role="region" aria-roledescription="карусель" aria-label="Наши услуги" data-carousel>
<div class="catalog-heading"><div><p class="catalog-kicker"><span></span>Инженерный подход · <?php echo esc_html(count($services)); ?> направления</p><h2>Направления и услуги под ключ</h2><p class="catalog-description">От строительства дома до благоустройства участка. Согласуем смету, подберём материалы и организуем каждый этап работ.</p></div>
<div class="carousel-controls" hidden><span class="carousel-status" aria-live="polite" aria-atomic="true"></span><button type="button" class="carousel-prev" aria-label="Предыдущая услуга" aria-controls="services-track"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h12"/></svg></button><button type="button" class="carousel-next" aria-label="Следующая услуга" aria-controls="services-track"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="m10 6 6 6-6 6M4 12h12"/></svg></button></div></div>
<div class="carousel-viewport" tabindex="0" aria-label="Карточки услуг. Используйте кнопки или клавиши влево и вправо."><div class="carousel-track" id="services-track">
<?php foreach ($services as $index => $service):
    $id = $service->ID;
    $image = (int) get_post_meta($id, 'service_image', true);
    $badge = get_post_meta($id, 'service_badge', true);
    $features = preg_split('/\R/u', (string) get_post_meta($id, 'service_features', true), -1, PREG_SPLIT_NO_EMPTY);
    $extra_label = get_post_meta($id, 'service_extra_label', true);
    $extra_value = get_post_meta($id, 'service_extra_value', true);
?>
<article class="card" role="group" aria-roledescription="слайд" aria-label="<?php echo esc_attr(($index + 1) . ' из ' . count($services)); ?>">
<div class="card-image"><?php if ($image && wp_attachment_is_image($image)): echo wp_get_attachment_image($image, 'large', false, ['loading' => 'lazy']); else: ?><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/house.svg'); ?>" alt="Архитектурная иллюстрация загородного дома" width="600" height="400" loading="lazy"><?php endif; ?>
<span class="catalog-image-tag"><i></i><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . ' / ' . (get_post_meta($id, 'service_tag', true) ?: get_the_title($id))); ?></span>
<?php if ($badge): ?><span class="catalog-badge"><svg width="12" height="14" viewBox="0 0 16 18" aria-hidden="true"><path d="M8 1 2 3v5c0 4 6 8 6 8s6-4 6-8V3Z"/><path d="m5 8 2 2 4-4"/></svg><?php echo esc_html($badge); ?></span><?php endif; ?></div>
<div class="card-body"><h3><?php echo esc_html(get_the_title($id)); ?></h3><p><?php echo esc_html(get_post_meta($id, 'service_description', true)); ?></p>
<div class="catalog-specs"><div><span>Стоимость работ:</span><strong class="catalog-price"><?php echo esc_html(get_post_meta($id, 'service_price', true) ?: 'По смете'); ?></strong></div><div><span>Срок выполнения:</span><strong><?php echo esc_html(get_post_meta($id, 'service_duration', true) ?: 'По проекту'); ?></strong></div>
<?php if ($extra_label || $extra_value): ?><div class="catalog-extra"><span><?php echo esc_html($extra_label); ?></span><strong><?php echo esc_html($extra_value); ?></strong></div><?php endif; ?></div>
<?php if ($features): ?><details class="catalog-details"><summary>Что входит в работы</summary><ul><?php foreach ($features as $feature): ?><li><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul></details><?php endif; ?>
<a href="<?php echo esc_url(get_post_meta($id, 'service_link', true) ?: '#request'); ?>" class="button"><?php echo esc_html(get_post_meta($id, 'service_button', true) ?: 'Обсудить проект'); ?></a></div></article>
<?php endforeach; ?>
</div></div><div class="catalog-footer"><div class="carousel-dots" aria-label="Выбор услуги"></div><a href="#request" class="catalog-note"><span aria-hidden="true">▧</span> Обсудим ваш проект и подготовим смету</a></div></div>
