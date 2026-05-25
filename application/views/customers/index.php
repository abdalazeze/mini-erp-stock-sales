<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('customers_title') ?></h4>
    <a href="<?= base_url('customers/add') ?>" class="btn btn-primary btn-sm"><?= lang('btn_add_new') ?></a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_id') ?></th>
                    <th><?= lang('lbl_name') ?></th>
                    <th><?= lang('lbl_phone') ?></th>
                    <th><?= lang('lbl_status') ?></th>
                    <th><?= lang('lbl_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($customers): ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?= $c->id ?></td>
                        <td><?= htmlspecialchars($c->name) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($c->phone ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $c->is_active ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $c->is_active ? lang('lbl_active') : lang('lbl_inactive') ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url("customers/edit/{$c->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                <?= lang('btn_edit') ?>
                            </a>
                            <a href="<?= base_url("customers/toggle/{$c->id}") ?>"
                               class="btn btn-sm <?= $c->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                <?= $c->is_active ? lang('btn_disable') : lang('btn_enable') ?>
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