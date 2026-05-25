<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
    private function _apply_filters($filters)
    {
        if (!empty($filters['search'])) {
            $this->db->group_start()
                     ->like('p.name', $filters['search'])
                     ->or_like('p.code', $filters['search'])
                     ->group_end();
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', $filters['category_id']);
        }
    }

    public function get_list($filters = [], $limit = 15, $offset = 0)
    {
        $this->db->select('p.*, c.name AS category_name')
                 ->from('products p')
                 ->join('categories c', 'c.id = p.category_id');
        $this->_apply_filters($filters);
        return $this->db->order_by('p.name')->limit($limit, $offset)->get()->result();
    }

    public function count_list($filters = [])
    {
        $this->db->from('products p')
                 ->join('categories c', 'c.id = p.category_id');
        $this->_apply_filters($filters);
        return $this->db->count_all_results();
    }

    public function get($id)
    {
        return $this->db->get_where('products', ['id' => $id])->row();
    }

    public function get_active()
    {
        return $this->db->where('is_active', 1)->order_by('name')->get('products')->result();
    }

    public function insert($data)
    {
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update('products', $data);
    }

    public function toggle_active($id)
    {
        $p = $this->get($id);
        $this->db->where('id', $id)->update('products', ['is_active' => $p->is_active ? 0 : 1]);
    }

    public function code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', $code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('products')->num_rows() > 0;
    }
}