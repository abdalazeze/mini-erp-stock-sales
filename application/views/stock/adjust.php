<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><?= lang('stock_adjust') ?></div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-5"><?= lang('lbl_product') ?></dt>
                    <dd class="col-7">
                        <code><?= htmlspecialchars($product->code) ?></code>
                        <?= htmlspecialchars($product->name) ?>
                    </dd>
                    <dt class="col-5"><?= lang('lbl_warehouse') ?></dt>
                    <dd class="col-7"><?= htmlspecialchars($warehouse->name) ?></dd>
                    <dt class="col-5"><?= lang('lbl_current_qty') ?></dt>
                    <dd class="col-7 fw-bold"><?= $stock_row ? $stock_row->quantity : 0 ?></dd>
                </dl>

                <?= form_open("stock/adjust/{$product->id}/{$warehouse->id}") ?>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('lbl_adjustment') ?></label>
                        <input type="number" name="adjustment"
                               class="form-control <?= form_error('adjustment') ? 'is-invalid' : '' ?>"
                               value="<?= set_value('adjustment', 0) ?>"
                               placeholder="+10 or -5">
                        <div class="form-text"><?= lang('lbl_current_qty') ?>: <?= $stock_row ? $stock_row->quantity : 0 ?></div>
                        <?php if (form_error('adjustment')): ?>
                            <div class="invalid-feedback"><?= form_error('adjustment') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= lang('btn_save') ?></button>
                        <a href="<?= base_url('stock') ?>" class="btn btn-outline-secondary"><?= lang('btn_cancel') ?></a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
