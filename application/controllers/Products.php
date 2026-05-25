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

    public function add()
    {
        $this->_admin_only();
        $this->_form();
    }

    public function edit($id)
    {
        $this->_admin_only();
        $product = $this->product_model->get($id);
        if (!$product) {
            show_404();
        }
        $this->_form($product);
    }

    public function toggle($id)
    {
        $this->_admin_only();
        $this->product_model->toggle_active($id);
        redirect('products');
    }

    private function _form($product = null)
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('code',           lang('lbl_code'),     'required|trim|max_length[50]');
            $this->form_validation->set_rules('name',           lang('lbl_name'),     'required|trim|max_length[200]');
            $this->form_validation->set_rules('category_id',   lang('lbl_category'), 'required|integer');
            $this->form_validation->set_rules('price',         lang('lbl_price'),    'required|decimal|greater_than[0]');
            $this->form_validation->set_rules('alert_quantity', lang('lbl_alert_qty'), 'required|integer|greater_than_equal_to[0]');

            if ($this->form_validation->run()) {
                $code = $this->input->post('code', true);

                if ($this->product_model->code_exists($code, $product ? $product->id : null)) {
                    $this->session->set_flashdata('error', 'Product code already in use.');
                } else {
                    $row = [
                        'code'           => $code,
                        'name'           => $this->input->post('name', true),
                        'category_id'    => (int) $this->input->post('category_id'),
                        'price'          => round((float) $this->input->post('price'), 2),
                        'alert_quantity' => (int) $this->input->post('alert_quantity'),
                    ];
                    $product ? $this->product_model->update($product->id, $row)
                             : $this->product_model->insert($row);
                    $this->session->set_flashdata('success', lang('msg_saved'));
                    redirect('products');
                }
            }
        }

        $data['page_title'] = lang($product ? 'products_edit' : 'products_add');
        $data['product']    = $product;
        $data['categories'] = $this->category_model->all(true);
        $this->load->view('layouts/header', $data);
        $this->load->view('products/form', $data);
        $this->load->view('layouts/footer');
    }
}