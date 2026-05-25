<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('warehouses_title') ?></h4>
    <a href="<?= base_url('warehouses/add') ?>" class="btn btn-primary btn-sm"><?= lang('btn_add_new') ?></a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_id') ?></th>
                    <th><?= lang('lbl_name') ?></th>
                    <th><?= lang('lbl_code') ?></th>
                    <th><?= lang('lbl_status') ?></th>
                    <th><?= lang('lbl_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($warehouses): ?>
                <?php foreach ($warehouses as $w): ?>
                    <tr>
                        <td><?= $w->id ?></td>
                        <td><?= htmlspecialchars($w->name) ?></td>
                        <td><code><?= htmlspecialchars($w->code) ?></code></td>
                        <td>
                            <span class="badge <?= $w->is_active ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $w->is_active ? lang('lbl_active') : lang('lbl_inactive') ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url("warehouses/edit/{$w->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                <?= lang('btn_edit') ?>
                            </a>
                            <a href="<?= base_url("warehouses/toggle/{$w->id}") ?>"
                               class="btn btn-sm <?= $w->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                <?= $w->is_active ? lang('btn_disable') : lang('btn_enable') ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-muted text-center py-3"><?= lang('msg_no_records') ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>