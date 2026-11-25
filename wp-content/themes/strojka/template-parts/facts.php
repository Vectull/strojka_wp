<?php
$facts = get_posts(['post_type' => 'strojka_fact', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'ID' => 'ASC']]);
if (!$facts) { return; }
$icons = [
    'building' => '<path d="M4 21V3h11v18M2 21h20M15 11h5v10M7 6h1m3 0h1M7 9h1m3 0h1M7 12h1m3 0h1M7 15h1m3 0h1M8 21v-3h3v3M17 14h1m-1 3h1"/>',
    'machine' => '<path d="M4 21h12M9 21V10l4-5 5 2 3-3M9 10l5 3 3-5M13 5l-3-2-4 2 1 4 4 1M14 13v5h4M6 21v-5h6"/>',
    'shield' => '<path d="M12 3 5 6v6c0 5 7 9 7 9s7-4 7-9V6Z"/><path d="m9 12 2 2 4-5"/>',
    'checklist' => '<path d="M3 6h7M3 12h7M3 18h7m4-13 2 2 4-4m-6 8 6 6m0-6-6 6"/>',
];
?>
<section class="facts-section" aria-label="Показатели компании"><div class="wrap facts-grid">
<?php foreach ($facts as $fact): $id = $fact->ID; $icon = get_post_meta($id, 'fact_icon', true); ?>
<article class="fact-card"><div class="fact-heading"><h2><?php echo esc_html(get_the_title($id)); ?></h2><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><?php echo $icons[$icon] ?? $icons['building']; ?></svg></div>
<div class="fact-content"><p class="fact-value<?php echo get_post_meta($id, 'fact_serif', true) ? ' fact-serif' : ''; ?>"><span><?php echo nl2br(esc_html(get_post_meta($id, 'fact_value', true))); ?></span></p><h3><?php echo esc_html(get_post_meta($id, 'fact_subtitle', true)); ?></h3><p class="fact-description"><?php echo esc_html(get_post_meta($id, 'fact_description', true)); ?></p></div></article>
<?php endforeach; ?></div></section>
