<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><?= lang('stock_adjust') ?></div>
            <div class="card-body">
                <?= form_open('stock/add_entry') ?>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('lbl_product') ?></label>
                        <select name="product_id" class="form-select" required>
                            <option value=""></option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->code) ?> — <?= htmlspecialchars($p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('lbl_warehouse') ?></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value=""></option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= $w->id ?>"><?= htmlspecialchars($w->name) ?></option>
                            <?php endforeach; ?>
                        </select>
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