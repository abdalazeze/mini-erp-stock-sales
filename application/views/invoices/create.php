<script>
var INVOICE_CFG = {
    searchUrl:  '<?= base_url('invoices/search_product') ?>',
    isAdmin:    <?= $is_admin ? 'true' : 'false' ?>,
    noLines:    '<?= lang('invoice_no_lines') ?>',
    noResults:  '<?= lang('msg_no_records') ?>'
};
</script>

<h4 class="mb-3"><?= lang('invoices_new') ?></h4>

<?= form_open('invoices/save', ['id' => 'invoice-form']) ?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label"><?= lang('lbl_customer') ?></label>
        <select name="customer_id" class="form-select" required>
            <option value=""></option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label"><?= lang('lbl_warehouse') ?></label>
        <?php if ($is_admin): ?>
            <select name="warehouse_id" class="form-select" required>
                <option value=""></option>
                <?php foreach ($warehouses as $w): ?>
                    <option value="<?= $w->id ?>"><?= htmlspecialchars($w->name) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <?php $wh = $warehouses[0] ?? null; ?>
            <input type="text" class="form-control" value="<?= htmlspecialchars($wh->name ?? '') ?>" disabled>
            <input type="hidden" name="warehouse_id" value="<?= $this->user->warehouse_id ?>">
        <?php endif; ?>
    </div>

    <div class="col-md-2">
        <label class="form-label"><?= lang('lbl_discount_pct') ?></label>
        <input type="number" name="discount_percent" id="discount-percent"
               class="form-control" value="0" min="0" max="100" step="0.01">
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="position-relative" id="search-wrapper">
            <input type="text" id="product-search" class="form-control form-control-sm"
                   placeholder="<?= lang('invoice_search_product') ?>" autocomplete="off">
            <div id="search-results" class="dropdown-menu w-100 shadow-sm" style="display:none;max-height:240px;overflow-y:auto"></div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('lbl_product') ?></th>
                    <th style="width:100px"><?= lang('lbl_qty') ?></th>
                    <th style="width:130px"><?= lang('lbl_unit_price') ?></th>
                    <th class="text-end" style="width:110px"><?= lang('lbl_line_total') ?></th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody id="lines-body"></tbody>
        </table>
    </div>
</div>

<div class="row justify-content-end mb-4">
    <div class="col-md-3">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted"><?= lang('lbl_subtotal') ?></td>
                <td class="text-end fw-bold" id="subtotal">0.00</td>
            </tr>
            <tr>
                <td class="text-muted"><?= lang('lbl_discount_amt') ?></td>
                <td class="text-end text-danger" id="discount-amount">0.00</td>
            </tr>
            <tr class="border-top">
                <td class="fw-bold"><?= lang('lbl_total') ?></td>
                <td class="text-end fw-bold fs-5" id="total">0.00</td>
            </tr>
        </table>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><?= lang('btn_save') ?></button>
    <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary"><?= lang('btn_cancel') ?></a>
</div>

<?= form_close() ?>
