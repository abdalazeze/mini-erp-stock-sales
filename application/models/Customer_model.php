<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
    public function all($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('name')->get('customers')->result();
    }

    public function get($id)
    {
        return $this->db->get_where('customers', ['id' => $id])->row();
    }

    public function insert($data)
    {
        $this->db->insert('customers', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update('customers', $data);
    }

    public function toggle_active($id)
    {
        $c = $this->get($id);
        $this->db->where('id', $id)->update('customers', ['is_active' => $c->is_active ? 0 : 1]);
    }
}
