<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('reports_low_stock') ?></h4>
    <a href="<?= base_url('reports/low_stock_csv') ?>?<?= http_build_query(array_filter(['warehouse_id' => $warehouse_id, 'search' => $search])) ?>"
       class="btn btn-outline-success btn-sm">
        <?= lang('btn_export_csv') ?>
    </a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <?= form_open('reports/low_stock', ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
            <?php if ($this->auth_lib->is_admin()): ?>
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value=""><?= lang('lbl_all_warehouses') ?></option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= $w->id ?>" <?= $warehouse_id == $w->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($w->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="<?= lang('lbl_name') ?> / <?= lang('lbl_code') ?>"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><?= lang('btn_search') ?></button>
                <a href="<?= base_url('reports/low_stock') ?>" class="btn btn-link btn-sm"><?= lang('lbl_all') ?></a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_code') ?></th>
                    <th><?= lang('lbl_name') ?></th>
                    <th><?= lang('lbl_category') ?></th>
                    <?php if ($this->auth_lib->is_admin()): ?>
                        <th><?= lang('lbl_warehouse') ?></th>
                    <?php endif; ?>
                    <th class="text-center"><?= lang('lbl_current_qty') ?></th>
                    <th class="text-center"><?= lang('lbl_alert_quantity') ?></th>
                    <th class="text-center text-danger"><?= lang('lbl_shortage') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows): ?>
                <?php foreach ($rows as $row): ?>
                    <tr class="<?= $row->quantity == 0 ? 'table-danger' : 'table-warning' ?>">
                        <td><code><?= htmlspecialchars($row->product_code) ?></code></td>
                        <td><?= htmlspecialchars($row->product_name) ?></td>
                        <td><?= htmlspecialchars($row->category_name) ?></td>
                        <?php if ($this->auth_lib->is_admin()): ?>
                            <td><?= htmlspecialchars($row->warehouse_name) ?></td>
                        <?php endif; ?>
                        <td class="text-center fw-bold <?= $row->quantity == 0 ? 'text-danger' : '' ?>">
                            <?= $row->quantity ?>
                        </td>
                        <td class="text-center text-muted"><?= $row->alert_quantity ?></td>
                        <td class="text-center fw-bold text-danger"><?= $row->shortage ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $this->auth_lib->is_admin() ? 7 : 6 ?>" class="text-success text-center py-3">
                        &#10003; <?= lang('msg_no_records') ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($rows): ?>
        <div class="card-footer text-muted small">
            <?= count($rows) ?> <?= lang('reports_low_stock') ?>
        </div>
    <?php endif; ?>
</div>