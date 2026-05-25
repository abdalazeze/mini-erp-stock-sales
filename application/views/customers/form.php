<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><?= lang($customer ? 'customers_edit' : 'customers_add') ?></div>
            <div class="card-body">
                <?= form_open($customer ? "customers/edit/{$customer->id}" : 'customers/add') ?>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('lbl_name') ?></label>
                        <input type="text" name="name"
                               class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                               value="<?= set_value('name', $customer->name ?? '') ?>" required>
                        <?php if (form_error('name')): ?>
                            <div class="invalid-feedback"><?= form_error('name') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            <?= lang('lbl_phone') ?>
                            <span class="text-muted small">(<?= lang('lbl_all') ?>)</span>
                        </label>
                        <input type="text" name="phone"
                               class="form-control <?= form_error('phone') ? 'is-invalid' : '' ?>"
                               value="<?= set_value('phone', $customer->phone ?? '') ?>">
                        <?php if (form_error('phone')): ?>
                            <div class="invalid-feedback"><?= form_error('phone') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= lang('btn_save') ?></button>
                        <a href="<?= base_url('customers') ?>" class="btn btn-outline-secondary"><?= lang('btn_cancel') ?></a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>