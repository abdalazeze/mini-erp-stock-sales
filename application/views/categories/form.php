<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><?= lang($category ? 'categories_edit' : 'categories_add') ?></div>
            <div class="card-body">
                <?= form_open($category ? "categories/edit/{$category->id}" : 'categories/add') ?>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('lbl_name') ?></label>
                        <input type="text" name="name" class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                               value="<?= set_value('name', $category->name ?? '') ?>" required>
                        <?php if (form_error('name')): ?>
                            <div class="invalid-feedback"><?= form_error('name') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= lang('btn_save') ?></button>
                        <a href="<?= base_url('categories') ?>" class="btn btn-outline-secondary"><?= lang('btn_cancel') ?></a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>