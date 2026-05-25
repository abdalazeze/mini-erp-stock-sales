<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_service
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function create($data)
    {
        $lines        = $data['lines'];
        $warehouse_id = (int) $data['warehouse_id'];
        $discount_pct = round((float) $data['discount_percent'], 2);

        $this->CI->db->trans_begin();

        // Atomic conditional stock decrement for each line.
        //
        // Why this pattern instead of SELECT then UPDATE:
        // Two concurrent requests can both read quantity = 1, both pass the
        // check, and both decrement — leaving quantity at -1. The UPDATE below
        // collapses the read and the check into one atomic operation: MySQL only
        // decrements the row when quantity >= qty holds at the moment the lock
        // is granted. The second request finds affected_rows = 0 and rolls back.
        foreach ($lines as $line) {
            $pid = (int) $line['product_id'];
            $qty = (int) $line['qty'];

            $this->CI->db->query(
                'UPDATE stock SET quantity = quantity - ?
                 WHERE product_id = ? AND warehouse_id = ? AND quantity >= ?',
                [$qty, $pid, $warehouse_id, $qty]
            );

            if ($this->CI->db->affected_rows() === 0) {
                $this->CI->db->trans_rollback();
                $product = $this->CI->product_model->get($pid);
                $name    = $product ? $product->name : "ID:{$pid}";
                return ['error' => lang('err_insufficient_stock') . $name];
            }
        }

        // Compute totals server-side — client totals are display only
        $subtotal      = 0;
        $insert_lines  = [];
        foreach ($lines as $line) {
            $lt = round((float) $line['unit_price'] * (int) $line['qty'], 2);
            $subtotal += $lt;
            $insert_lines[] = [
                'product_id' => (int) $line['product_id'],
                'qty'        => (int) $line['qty'],
                'unit_price' => round((float) $line['unit_price'], 2),
                'line_total' => $lt,
            ];
        }
        $subtotal        = round($subtotal, 2);
        $discount_amount = round($subtotal * $discount_pct / 100, 2);
        $total           = round($subtotal - $discount_amount, 2);

        // Insert with a temp unique invoice_no so the UNIQUE constraint is
        // satisfied before we know the auto-increment id.
        $this->CI->db->insert('invoices', [
            'invoice_no'       => uniqid('INV', true),
            'customer_id'      => (int) $data['customer_id'],
            'warehouse_id'     => $warehouse_id,
            'user_id'          => (int) $data['user_id'],
            'subtotal'         => $subtotal,
            'discount_percent' => $discount_pct,
            'discount_amount'  => $discount_amount,
            'total'            => $total,
        ]);

        $invoice_id = $this->CI->db->insert_id();
        $invoice_no = 'INV-' . date('Y') . '-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);
        $this->CI->db->where('id', $invoice_id)->update('invoices', ['invoice_no' => $invoice_no]);

        foreach ($insert_lines as $il) {
            $this->CI->db->insert('invoice_lines', ['invoice_id' => $invoice_id] + $il);
        }

        $this->CI->db->trans_commit();

        return ['invoice_id' => $invoice_id, 'invoice_no' => $invoice_no];
    }
}
