<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= lang('invoices_title') ?></h4>
    <a href="<?= base_url('invoices/create') ?>" class="btn btn-primary btn-sm"><?= lang('invoices_new') ?></a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_invoice_no') ?></th>
                    <th><?= lang('lbl_customer') ?></th>
                    <th><?= lang('lbl_warehouse') ?></th>
                    <th><?= lang('lbl_date') ?></th>
                    <th class="text-end"><?= lang('lbl_total') ?></th>
                    <th><?= lang('lbl_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($invoices): ?>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($inv->invoice_no) ?></code></td>
                        <td><?= htmlspecialchars($inv->customer_name) ?></td>
                        <td><?= htmlspecialchars($inv->warehouse_name) ?></td>
                        <td class="text-muted small"><?= date('Y-m-d H:i', strtotime($inv->created_at)) ?></td>
                        <td class="text-end fw-bold"><?= number_format($inv->total, 2) ?></td>
                        <td>
                            <a href="<?= base_url("invoices/view/{$inv->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                <?= lang('btn_view') ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-muted text-center py-3"><?= lang('msg_no_records') ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted"><?= $total ?> records</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('invoices') ?>?page=<?= $page - 1 ?>">&#8249;</a>
                </li>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('invoices') ?>?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('invoices') ?>?page=<?= $page + 1 ?>">&#8250;</a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>