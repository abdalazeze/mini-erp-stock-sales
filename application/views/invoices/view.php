<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= htmlspecialchars($invoice->invoice_no) ?></h4>
    <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm">&larr; <?= lang('invoices_title') ?></a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5"><?= lang('lbl_invoice_no') ?></dt>
                    <dd class="col-7"><code><?= htmlspecialchars($invoice->invoice_no) ?></code></dd>

                    <dt class="col-5"><?= lang('lbl_customer') ?></dt>
                    <dd class="col-7"><?= htmlspecialchars($invoice->customer_name) ?></dd>

                    <dt class="col-5"><?= lang('lbl_warehouse') ?></dt>
                    <dd class="col-7"><?= htmlspecialchars($invoice->warehouse_name) ?></dd>

                    <dt class="col-5"><?= lang('lbl_date') ?></dt>
                    <dd class="col-7 text-muted"><?= date('Y-m-d H:i', strtotime($invoice->created_at)) ?></dd>

                    <dt class="col-5">By</dt>
                    <dd class="col-7 text-muted"><?= htmlspecialchars($invoice->username) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-3 offset-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted"><?= lang('lbl_subtotal') ?></td>
                        <td class="text-end"><?= number_format($invoice->subtotal, 2) ?></td>
                    </tr>
                    <?php if ($invoice->discount_percent > 0): ?>
                    <tr>
                        <td class="text-muted"><?= lang('lbl_discount_pct') ?> (<?= $invoice->discount_percent ?>%)</td>
                        <td class="text-end text-danger">-<?= number_format($invoice->discount_amount, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="border-top">
                        <td class="fw-bold"><?= lang('lbl_total') ?></td>
                        <td class="text-end fw-bold fs-5"><?= number_format($invoice->total, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_code') ?></th>
                    <th><?= lang('lbl_product') ?></th>
                    <th class="text-center"><?= lang('lbl_qty') ?></th>
                    <th class="text-end"><?= lang('lbl_unit_price') ?></th>
                    <th class="text-end"><?= lang('lbl_line_total') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td><code><?= htmlspecialchars($line->product_code) ?></code></td>
                    <td><?= htmlspecialchars($line->product_name) ?></td>
                    <td class="text-center"><?= $line->qty ?></td>
                    <td class="text-end"><?= number_format($line->unit_price, 2) ?></td>
                    <td class="text-end"><?= number_format($line->line_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
