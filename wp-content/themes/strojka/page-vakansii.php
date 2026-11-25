<?php
get_header();
the_post();
$page_id = get_the_ID();
$tags = preg_split('/\R/u', get_post_meta($page_id, 'careers_tags', true) ?: "З/П от 85 000 до 160 000 ₽\nОплата без задержек\nИногородним жильё", -1, PREG_SPLIT_NO_EMPTY);
$status = sanitize_key($_GET['application'] ?? '');
?>
<main id="main" class="jobs-page"><section class="jobs-hero"><div class="wrap"><a class="jobs-back" href="<?php echo esc_url(home_url('/')); ?>">Главная / Работа в компании</a><div class="jobs-hero-grid"><div><p class="careers-kicker"><i></i>Работа в ТулаСтройГрупп</p><h1>Вакансии<br><em>в сильной команде</em></h1><p class="jobs-hero-description">Строим дома и обустраиваем участки в Туле и области. Ищем людей, которые знают своё дело и отвечают за результат.</p><div class="jobs-hero-actions"><a class="button" href="#apply">Откликнуться на вакансию</a><a class="jobs-secondary" href="#open-positions">Посмотреть направления</a></div></div><aside class="jobs-summary"><p class="jobs-summary-label">Условия работы</p><h2>Ваше мастерство.<br>Наша поддержка.</h2><ul><?php foreach ($tags as $tag): ?><li><span aria-hidden="true">✓</span><?php echo esc_html($tag); ?></li><?php endforeach; ?></ul><p class="jobs-summary-note">Инструмент, спецодежда и доставка до объекта — от компании.</p></aside></div></div></section>
<section class="jobs-openings" id="open-positions"><div class="wrap"><p class="contact-kicker">Найдите своё направление</p><div class="jobs-editor">
<?php
$content = apply_filters('the_content', get_the_content());
$content = str_replace('в форме отклика на главной странице.', 'в форме ниже.', $content);
$content = str_replace(esc_url(home_url('/#careers')), '#apply', $content);
$content = preg_replace_callback('~<li>(.*?)</li>~s', function ($match) {
    $parts = preg_split('/\s+[—–]\s+/u', $match[1], 2);
    $title = wp_strip_all_tags($parts[0]);
    return '<li class="job-card"><span class="job-card-label">Тула и область · Строительство</span><h3>' . wp_kses_post($parts[0]) . '</h3>' . (isset($parts[1]) ? '<p>' . wp_kses_post($parts[1]) . '</p>' : '') . '<a href="#apply" data-job="' . esc_attr($title) . '">Обсудить вакансию</a></li>';
}, $content);
echo $content;
?></div></div></section>
<section class="jobs-apply-section" id="apply"><div class="wrap jobs-apply-layout"><div class="jobs-apply-copy"><p class="careers-kicker"><i></i>Давайте знакомиться</p><h2>Умеете работать руками<br>и держать слово?<br><em>Нам по пути.</em></h2><p>Расскажите о своей специальности и опыте. Обсудим задачи, объекты и условия работы.</p><div class="jobs-call"><small>Есть вопросы? Позвоните</small><?php $phone = get_theme_mod('strojka_phone', '+7 (4872) 75-30-20'); ?><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></div></div><div class="jobs-application engineer-form"><p class="engineer-kicker">Первый шаг к работе в команде</p><h2>Отклик на вакансию</h2><p class="engineer-intro">Оставьте контакты — свяжемся и обсудим детали.</p>
<?php if ($status === 'saved'): ?><div class="notice" role="status">Спасибо! Ваш отклик сохранён.</div><?php elseif ($status === 'error'): ?><div class="notice error" role="alert">Проверьте имя, телефон и согласие, затем отправьте отклик ещё раз.</div><?php endif; ?>
<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="strojka_request"><input type="hidden" name="lead_kind" value="application_page"><input type="hidden" name="service" value="Отклик на вакансию"><?php wp_nonce_field('strojka_request', 'strojka_nonce'); ?><div class="hp" aria-hidden="true"><label>Ваш сайт<input name="website" tabindex="-1" autocomplete="off"></label></div><label>Ваше имя<input name="client_name" autocomplete="name" maxlength="100" placeholder="Как к вам обращаться" required></label><label>Номер телефона<input name="client_phone" type="tel" autocomplete="tel" placeholder="+7 (___) ___-__-__" pattern="[+0-9 ()\-]{7,30}" maxlength="30" required></label><label>Специальность и опыт<textarea name="message" maxlength="3000" placeholder="Кем хотите работать? Расскажите о вашем опыте."></textarea></label><label class="consent"><input type="checkbox" name="consent" value="1" required><span>Согласен на обработку указанных данных для ответа на отклик. <?php if (get_privacy_policy_url()): ?><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Политика конфиденциальности</a><?php endif; ?></span></label><button class="button jobs-submit" type="submit">Отправить отклик</button></form></div></div></section></main>
<?php get_footer(); ?>
