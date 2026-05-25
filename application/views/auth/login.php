<?php
$_lang   = current_lang();
$_is_rtl = $_lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $_lang ?>" dir="<?= $_is_rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('auth_sign_in') ?> — <?= lang('app_name') ?></title>
    <?php if ($_is_rtl): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <?php endif; ?>
    <style>
        body { background: #f0f2f5; }
        .login-card { max-width: 380px; margin: 100px auto; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h5 class="card-title mb-4 text-center fw-bold"><?= lang('app_name') ?></h5>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?= form_open('auth/login') ?>
                <div class="mb-3">
                    <label class="form-label"><?= lang('auth_username') ?></label>
                    <input type="text" name="username" class="form-control"
                           value="<?= set_value('username') ?>" autofocus required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= lang('auth_password') ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100"><?= lang('auth_sign_in') ?></button>
            <?= form_close() ?>

            <div class="mt-3 text-center">
                <a href="<?= base_url('lang/set/en') ?>" class="text-decoration-none small <?= $_lang === 'en' ? 'fw-bold' : 'text-muted' ?>">EN</a>
                &nbsp;|&nbsp;
                <a href="<?= base_url('lang/set/ar') ?>" class="text-decoration-none small <?= $_lang === 'ar' ? 'fw-bold' : 'text-muted' ?>">AR</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>