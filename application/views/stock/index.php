<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('stock_title') ?></h4>
    <?php if ($this->auth_lib->is_admin()): ?>
        <a href="<?= base_url('stock/add_entry') ?>" class="btn btn-primary btn-sm"><?= lang('btn_add_new') ?></a>
    <?php endif; ?>
</div>

<?php if ($this->auth_lib->is_admin()): ?>
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <?= form_open('stock', ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-4">
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value=""><?= lang('lbl_all_warehouses') ?></option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w->id ?>" <?= $warehouse_id == $w->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($w->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><?= lang('btn_search') ?></button>
                <a href="<?= base_url('stock') ?>" class="btn btn-link btn-sm"><?= lang('lbl_all') ?></a>
            </div>
        <?= form_close() ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_code') ?></th>
                    <th><?= lang('lbl_name') ?></th>
                    <?php if ($this->auth_lib->is_admin()): ?>
                        <th><?= lang('lbl_warehouse') ?></th>
                    <?php endif; ?>
                    <th class="text-center"><?= lang('lbl_current_qty') ?></th>
                    <th class="text-center"><?= lang('lbl_alert_quantity') ?></th>
                    <?php if ($this->auth_lib->is_admin()): ?>
                        <th><?= lang('lbl_actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($stock): ?>
                <?php foreach ($stock as $row): ?>
                    <?php $low = $row->quantity <= $row->alert_quantity; ?>
                    <tr class="<?= $low ? 'table-warning' : '' ?>">
                        <td><code><?= htmlspecialchars($row->product_code) ?></code></td>
                        <td><?= htmlspecialchars($row->product_name) ?></td>
                        <?php if ($this->auth_lib->is_admin()): ?>
                            <td><?= htmlspecialchars($row->warehouse_name) ?></td>
                        <?php endif; ?>
                        <td class="text-center fw-bold <?= $low ? 'text-danger' : '' ?>">
                            <?= $row->quantity ?>
                        </td>
                        <td class="text-center text-muted"><?= $row->alert_quantity ?></td>
                        <?php if ($this->auth_lib->is_admin()): ?>
                            <td>
                                <a href="<?= base_url("stock/adjust/{$row->product_id}/{$row->warehouse_id}") ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <?= lang('stock_adjust') ?>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $this->auth_lib->is_admin() ? 6 : 4 ?>" class="text-muted text-center py-3">
                        <?= lang('msg_no_records') ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
