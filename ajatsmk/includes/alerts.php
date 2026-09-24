<?php
/**
 * Alert / Flash Message Component
 */
$flash = get_flash();
if ($flash):
    $alertType = $flash['type'] ?? 'info';
    $icon = match($alertType) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default   => 'bi-info-circle-fill'
    };
?>
<div class="alert alert-<?= e($alertType) ?> alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 py-3 px-4 my-3" role="alert">
    <i class="bi <?= $icon ?> fs-4 me-3"></i>
    <div class="flex-grow-1">
        <?= $flash['message'] ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
