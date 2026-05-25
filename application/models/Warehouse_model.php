<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_model extends CI_Model
{
    public function all($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('name')->get('warehouses')->result();
    }

    public function get($id)
    {
        return $this->db->get_where('warehouses', ['id' => $id])->row();
    }

    public function insert($data)
    {
        $this->db->insert('warehouses', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update('warehouses', $data);
    }

    public function toggle_active($id)
    {
        $w = $this->get($id);
        $this->db->where('id', $id)->update('warehouses', ['is_active' => $w->is_active ? 0 : 1]);
    }

    public function code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', $code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('warehouses')->num_rows() > 0;
    }
}
