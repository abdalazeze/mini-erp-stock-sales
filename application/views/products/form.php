<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><?= lang($product ? 'products_edit' : 'products_add') ?></div>
            <div class="card-body">
                <?= form_open($product ? "products/edit/{$product->id}" : 'products/add') ?>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label"><?= lang('lbl_code') ?></label>
                            <input type="text" name="code"
                                   class="form-control <?= form_error('code') ? 'is-invalid' : '' ?>"
                                   value="<?= set_value('code', $product->code ?? '') ?>" required>
                            <?php if (form_error('code')): ?>
                                <div class="invalid-feedback"><?= form_error('code') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label"><?= lang('lbl_name') ?></label>
                            <input type="text" name="name"
                                   class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                                   value="<?= set_value('name', $product->name ?? '') ?>" required>
                            <?php if (form_error('name')): ?>
                                <div class="invalid-feedback"><?= form_error('name') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= lang('lbl_category') ?></label>
                            <select name="category_id"
                                    class="form-select <?= form_error('category_id') ? 'is-invalid' : '' ?>" required>
                                <option value=""></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"
                                        <?= set_select('category_id', $cat->id, isset($product) && $product->category_id == $cat->id) ?>>
                                        <?= htmlspecialchars($cat->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (form_error('category_id')): ?>
                                <div class="invalid-feedback"><?= form_error('category_id') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= lang('lbl_price') ?></label>
                            <input type="number" name="price" step="0.01" min="0.01"
                                   class="form-control <?= form_error('price') ? 'is-invalid' : '' ?>"
                                   value="<?= set_value('price', $product->price ?? '') ?>" required>
                            <?php if (form_error('price')): ?>
                                <div class="invalid-feedback"><?= form_error('price') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= lang('lbl_alert_qty') ?></label>
                            <input type="number" name="alert_quantity" min="0"
                                   class="form-control <?= form_error('alert_quantity') ? 'is-invalid' : '' ?>"
                                   value="<?= set_value('alert_quantity', $product->alert_quantity ?? 0) ?>" required>
                            <?php if (form_error('alert_quantity')): ?>
                                <div class="invalid-feedback"><?= form_error('alert_quantity') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><?= lang('btn_save') ?></button>
                        <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary"><?= lang('btn_cancel') ?></a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>