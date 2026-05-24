<?php
/**
 * Cabecera de marca SaaS. AlCorte siempre arriba; debajo el nombre del negocio.
 */
$brand_subline = $brand_subline ?? '';
$brand_subline_muted = !empty($brand_subline_muted);
$brand_variant = $brand_variant ?? 'nav';
$headerClass = 'brand-header';
if ($brand_variant === 'center') {
    $headerClass .= ' brand-header--center';
}
?>
<div class="<?= $headerClass ?>">
    <p class="brand-alcorte-title">Al<span>Corte</span></p>

    <?php if (!empty($branding['has_logo'])): ?>
    <div class="brand-logo-wrap">
        <img src="<?= htmlspecialchars($branding['logo_url']) ?>"
             alt="<?= htmlspecialchars($branding['nombre_negocio'] ?: 'Logo') ?>"
             class="brand-logo"
             loading="eager"
             decoding="async">
    </div>
    <?php endif; ?>

    <div class="brand-text">
        <?php if (!empty($branding['has_nombre'])): ?>
        <p class="brand-business"><?= htmlspecialchars($branding['nombre_negocio']) ?></p>
        <?php endif; ?>

        <?php if ($brand_subline !== ''): ?>
        <p class="brand-subline<?= $brand_subline_muted ? ' brand-subline--muted' : '' ?>"><?= htmlspecialchars($brand_subline) ?></p>
        <?php endif; ?>
    </div>
</div>
