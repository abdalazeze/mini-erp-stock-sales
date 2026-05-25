$(function () {
    var cfg   = window.INVOICE_CFG || {};
    var lines = [];
    var searchTimer;

    // ── helpers ──────────────────────────────────────────────────────────────

    function esc(s) {
        return $('<span>').text(s).html();
    }

    function lineTotal(line) {
        return Math.round(line.qty * line.unit_price * 100) / 100;
    }

    function updateTotals() {
        var subtotal = lines.reduce(function (sum, l) { return sum + lineTotal(l); }, 0);
        var pct      = Math.min(100, Math.max(0, parseFloat($('#discount-percent').val()) || 0));
        var discAmt  = Math.round(subtotal * pct) / 100;
        $('#subtotal').text(subtotal.toFixed(2));
        $('#discount-amount').text(discAmt.toFixed(2));
        $('#total').text((subtotal - discAmt).toFixed(2));
    }

    // ── render ───────────────────────────────────────────────────────────────

    function renderLines() {
        var $tbody = $('#lines-body').empty();

        if (!lines.length) {
            $tbody.html('<tr><td colspan="5" class="text-muted text-center py-3">' + esc(cfg.noLines) + '</td></tr>');
            updateTotals();
            return;
        }

        $.each(lines, function (i, line) {
            var lt    = lineTotal(line).toFixed(2);
            var price = line.unit_price.toFixed(2);

            var priceCell = cfg.isAdmin
                ? '<input type="number" class="form-control form-control-sm price-input" '
                  + 'name="lines[' + i + '][unit_price]" value="' + price + '" step="0.01" min="0.01">'
                : esc(price) + '<input type="hidden" name="lines[' + i + '][unit_price]" value="' + price + '">';

            $tbody.append(
                '<tr data-idx="' + i + '">'
                + '<td>'
                +   '<input type="hidden" name="lines[' + i + '][product_id]" value="' + line.product_id + '">'
                +   '<code>' + esc(line.product_code) + '</code> ' + esc(line.product_name)
                + '</td>'
                + '<td><input type="number" class="form-control form-control-sm qty-input" '
                +   'name="lines[' + i + '][qty]" value="' + line.qty + '" min="1"></td>'
                + '<td>' + priceCell + '</td>'
                + '<td class="text-end line-total-cell">' + lt + '</td>'
                + '<td class="text-center">'
                +   '<button type="button" class="btn btn-sm btn-outline-danger remove-btn" data-idx="' + i + '">&times;</button>'
                + '</td>'
                + '</tr>'
            );
        });

        updateTotals();
    }

    // ── line events (delegated — tbody is re-rendered on add/remove) ─────────

    $('#lines-body').on('input change', '.qty-input', function () {
        var i = +$(this).closest('tr').data('idx');
        lines[i].qty = Math.max(1, parseInt($(this).val()) || 1);
        $(this).val(lines[i].qty);
        $(this).closest('tr').find('.line-total-cell').text(lineTotal(lines[i]).toFixed(2));
        updateTotals();
    });

    $('#lines-body').on('input change', '.price-input', function () {
        var i = +$(this).closest('tr').data('idx');
        lines[i].unit_price = Math.max(0.01, parseFloat($(this).val()) || 0.01);
        $(this).closest('tr').find('.line-total-cell').text(lineTotal(lines[i]).toFixed(2));
        updateTotals();
    });

    $('#lines-body').on('click', '.remove-btn', function () {
        lines.splice(+$(this).data('idx'), 1);
        renderLines();
    });

    $('#discount-percent').on('input change', updateTotals);

    // ── product search ───────────────────────────────────────────────────────

    $('#product-search').on('input', function () {
        clearTimeout(searchTimer);
        var q = $(this).val().trim();
        if (q.length < 2) { $('#search-results').hide(); return; }

        searchTimer = setTimeout(function () {
            $.getJSON(cfg.searchUrl, { q: q }, function (data) {
                var $menu = $('#search-results').empty();
                if (!data.length) {
                    $menu.html('<span class="dropdown-item-text text-muted small">' + esc(cfg.noResults) + '</span>').show();
                    return;
                }
                $.each(data, function (_, p) {
                    $('<a class="dropdown-item" href="#">')
                        .text(p.code + ' — ' + p.name + '  (' + parseFloat(p.price).toFixed(2) + ')')
                        .on('click', function (e) {
                            e.preventDefault();
                            addLine(p);
                            $('#product-search').val('');
                            $('#search-results').hide();
                        })
                        .appendTo($menu);
                });
                $menu.show();
            });
        }, 300);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#search-wrapper').length) {
            $('#search-results').hide();
        }
    });

    function addLine(product) {
        for (var i = 0; i < lines.length; i++) {
            if (lines[i].product_id == product.id) {
                // Already in list — bump qty instead of duplicating
                lines[i].qty++;
                $('#lines-body tr[data-idx="' + i + '"] .qty-input').val(lines[i].qty);
                $('#lines-body tr[data-idx="' + i + '"] .line-total-cell').text(lineTotal(lines[i]).toFixed(2));
                updateTotals();
                return;
            }
        }
        lines.push({
            product_id:   product.id,
            product_code: product.code,
            product_name: product.name,
            unit_price:   parseFloat(product.price),
            qty:          1,
        });
        renderLines();
    }

    // ── form submit guard ────────────────────────────────────────────────────

    $('#invoice-form').on('submit', function (e) {
        if (!lines.length) {
            e.preventDefault();
            alert(cfg.noLines);
        }
    });

    // Initial render
    renderLines();
});