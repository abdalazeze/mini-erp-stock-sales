<?php
$_lang   = current_lang();
$_is_rtl = $_lang === 'ar';
$_user   = isset($this->auth_lib) ? $this->auth_lib->user() : null;
?>
<!DOCTYPE html>
<html lang="<?= $_lang ?>" dir="<?= $_is_rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash() ?>">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?><?= lang('app_name') ?></title>
    <?php if ($_is_rtl): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="<?= $_is_rtl ? 'lang-ar' : 'lang-en' ?>">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url('products') ?>"><?= lang('app_name') ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php if ($_user && $_user->role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('categories') ?>"><?= lang('nav_categories') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('warehouses') ?>"><?= lang('nav_warehouses') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('customers') ?>"><?= lang('nav_customers') ?></a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('products') ?>"><?= lang('nav_products') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('stock') ?>"><?= lang('nav_stock') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('invoices') ?>"><?= lang('nav_invoices') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('reports/low_stock') ?>"><?= lang('nav_reports') ?></a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a href="<?= base_url('lang/set/en') ?>" class="nav-link <?= $_lang === 'en' ? 'fw-bold text-white' : 'text-secondary' ?>">EN</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('lang/set/ar') ?>" class="nav-link <?= $_lang === 'ar' ? 'fw-bold text-white' : 'text-secondary' ?>">AR</a>
                </li>
                <?php if ($_user): ?>
                    <li class="nav-item">
                        <span class="navbar-text text-secondary small"><?= htmlspecialchars($_user->username) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('auth/logout') ?>"><?= lang('nav_sign_out') ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid py-4 px-4">
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($this->session->flashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($this->session->flashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>