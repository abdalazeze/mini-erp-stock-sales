<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model
{
    public function get_list($warehouse_id = null)
    {
        $this->db->select('s.id, s.quantity, p.code AS product_code, p.name AS product_name,
                           p.alert_quantity, c.name AS category_name,
                           w.id AS warehouse_id, w.name AS warehouse_name')
                 ->from('stock s')
                 ->join('products p',    'p.id = s.product_id')
                 ->join('categories c',  'c.id = p.category_id')
                 ->join('warehouses w',  'w.id = s.warehouse_id')
                 ->where('p.is_active',  1)
                 ->where('w.is_active',  1);

        if ($warehouse_id) {
            $this->db->where('s.warehouse_id', $warehouse_id);
        }

        return $this->db->order_by('w.name')->order_by('p.name')->get()->result();
    }

    public function get_row($product_id, $warehouse_id)
    {
        return $this->db->get_where('stock', [
            'product_id'   => $product_id,
            'warehouse_id' => $warehouse_id,
        ])->row();
    }

    public function get_low_stock($warehouse_id = null, $search = '')
    {
        $this->db->select('p.code AS product_code, p.name AS product_name,
                           c.name AS category_name, w.name AS warehouse_name,
                           s.quantity, p.alert_quantity,
                           (p.alert_quantity - s.quantity) AS shortage')
                 ->from('stock s')
                 ->join('products p',   'p.id = s.product_id')
                 ->join('categories c', 'c.id = p.category_id')
                 ->join('warehouses w', 'w.id = s.warehouse_id')
                 ->where('s.quantity <=', 'p.alert_quantity', false)
                 ->where('p.is_active', 1)
                 ->where('w.is_active', 1);

        if ($warehouse_id) {
            $this->db->where('s.warehouse_id', $warehouse_id);
        }
        if ($search !== '') {
            $this->db->group_start()
                     ->like('p.name', $search)
                     ->or_like('p.code', $search)
                     ->group_end();
        }

        return $this->db->order_by('shortage', 'DESC')
                        ->order_by('p.name')
                        ->get()->result();
    }

    public function adjust($product_id, $warehouse_id, $delta)
    {
        $row = $this->get_row($product_id, $warehouse_id);
        if ($row) {
            $new_qty = max(0, $row->quantity + $delta);
            $this->db->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])
                     ->update('stock', ['quantity' => $new_qty]);
        } else {
            $this->db->insert('stock', [
                'product_id'   => $product_id,
                'warehouse_id' => $warehouse_id,
                'quantity'     => max(0, $delta),
            ]);
        }
    }
}