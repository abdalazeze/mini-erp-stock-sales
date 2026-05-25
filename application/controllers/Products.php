<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_model');
        $this->load->model('category_model');
    }

    public function index()
    {
        $per_page = 15;
        $page     = max(1, (int) $this->input->get('page'));
        $filters  = [
            'search'      => $this->input->get('search', true) ?: '',
            'category_id' => (int) $this->input->get('category_id'),
        ];

        $total  = $this->product_model->count_list($filters);
        $offset = ($page - 1) * $per_page;
        $pages  = $total > 0 ? (int) ceil($total / $per_page) : 1;

        $data['page_title'] = lang('products_title');
        $data['products']   = $this->product_model->get_list($filters, $per_page, $offset);
        $data['categories'] = $this->category_model->all(true);
        $data['filters']    = $filters;
        $data['total']      = $total;
        $data['page']       = $page;
        $data['pages']      = $pages;

        $this->load->view('layouts/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('layouts/footer');
    }
}