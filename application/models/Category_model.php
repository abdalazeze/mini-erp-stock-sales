<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
    public function all($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('name')->get('categories')->result();
    }

    public function get($id)
    {
        return $this->db->get_where('categories', ['id' => $id])->row();
    }

    public function insert($data)
    {
        $this->db->insert('categories', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update('categories', $data);
    }

    public function toggle_active($id)
    {
        $cat = $this->get($id);
        $this->db->where('id', $id)->update('categories', ['is_active' => $cat->is_active ? 0 : 1]);
    }
}