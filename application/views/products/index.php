<?php
$qs_base = http_build_query(array_filter([
    'search'      => $filters['search'],
    'category_id' => $filters['category_id'],
]));
$page_url = base_url('products') . '?' . ($qs_base ? $qs_base . '&' : '');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('products_title') ?></h4>
    <?php if ($this->auth_lib->is_admin()): ?>
        <a href="<?= base_url('products/add') ?>" class="btn btn-primary btn-sm"><?= lang('btn_add_new') ?></a>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <?= form_open('products', ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="<?= lang('lbl_name') ?> / <?= lang('lbl_code') ?>"
                       value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select form-select-sm">
                    <option value=""><?= lang('lbl_all_categories') ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= $filters['category_id'] == $cat->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><?= lang('btn_search') ?></button>
                <a href="<?= base_url('products') ?>" class="btn btn-link btn-sm"><?= lang('lbl_all') ?></a>
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
                    <th class="text-end"><?= lang('lbl_price') ?></th>
                    <th class="text-center"><?= lang('lbl_alert_qty') ?></th>
                    <th><?= lang('lbl_status') ?></th>
                    <?php if ($this->auth_lib->is_admin()): ?>
                        <th><?= lang('lbl_actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($products): ?>
                <?php foreach ($products as $p): ?>
                    <tr class="<?= !$p->is_active ? 'text-muted' : '' ?>">
                        <td><code><?= htmlspecialchars($p->code) ?></code></td>
                        <td><?= htmlspecialchars($p->name) ?></td>
                        <td><?= htmlspecialchars($p->category_name) ?></td>
                        <td class="text-end"><?= number_format($p->price, 2) ?></td>
                        <td class="text-center"><?= $p->alert_quantity ?></td>
                        <td>
                            <span class="badge <?= $p->is_active ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $p->is_active ? lang('lbl_active') : lang('lbl_inactive') ?>
                            </span>
                        </td>
                        <?php if ($this->auth_lib->is_admin()): ?>
                            <td>
                                <a href="<?= base_url("products/edit/{$p->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                    <?= lang('btn_edit') ?>
                                </a>
                                <a href="<?= base_url("products/toggle/{$p->id}") ?>"
                                   class="btn btn-sm <?= $p->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                    <?= $p->is_active ? lang('btn_disable') : lang('btn_enable') ?>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $this->auth_lib->is_admin() ? 7 : 6 ?>" class="text-muted text-center py-3">
                        <?= lang('msg_no_records') ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted"><?= $total ?> records</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_url ?>page=<?= $page - 1 ?>">&#8249;</a>
                </li>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $page_url ?>page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_url ?>page=<?= $page + 1 ?>">&#8250;</a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>