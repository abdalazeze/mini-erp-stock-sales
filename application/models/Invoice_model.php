<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends CI_Model
{
    public function get_list($warehouse_id = null, $limit = 20, $offset = 0)
    {
        $this->db->select('i.id, i.invoice_no, i.total, i.discount_percent, i.created_at,
                           c.name AS customer_name, w.name AS warehouse_name, u.username')
                 ->from('invoices i')
                 ->join('customers c',  'c.id = i.customer_id')
                 ->join('warehouses w', 'w.id = i.warehouse_id')
                 ->join('users u',      'u.id = i.user_id');

        if ($warehouse_id) {
            $this->db->where('i.warehouse_id', $warehouse_id);
        }

        return $this->db->order_by('i.created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->result();
    }

    public function count_list($warehouse_id = null)
    {
        $this->db->from('invoices i');
        if ($warehouse_id) {
            $this->db->where('i.warehouse_id', $warehouse_id);
        }
        return $this->db->count_all_results();
    }

    public function get($id, $warehouse_id = null)
    {
        $this->db->select('i.*, c.name AS customer_name, w.name AS warehouse_name, u.username')
                 ->from('invoices i')
                 ->join('customers c',  'c.id = i.customer_id')
                 ->join('warehouses w', 'w.id = i.warehouse_id')
                 ->join('users u',      'u.id = i.user_id')
                 ->where('i.id', $id);

        if ($warehouse_id) {
            $this->db->where('i.warehouse_id', $warehouse_id);
        }

        return $this->db->get()->row();
    }

    public function get_lines($invoice_id)
    {
        return $this->db->select('il.*, p.code AS product_code, p.name AS product_name')
                        ->from('invoice_lines il')
                        ->join('products p', 'p.id = il.product_id')
                        ->where('il.invoice_id', $invoice_id)
                        ->get()->result();
    }
}
