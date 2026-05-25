<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('stock_model');
        $this->load->model('warehouse_model');
        $this->load->model('product_model');
    }

    public function index()
    {
        // user_warehouse is always scoped to their warehouse — ignore GET param
        if ($this->user->role === 'user_warehouse') {
            $warehouse_id = (int) $this->user->warehouse_id;
        } else {
            $warehouse_id = (int) $this->input->get('warehouse_id');
        }

        $data['page_title']   = lang('stock_title');
        $data['stock']        = $this->stock_model->get_list($warehouse_id ?: null);
        $data['warehouses']   = $this->auth_lib->is_admin() ? $this->warehouse_model->all(true) : [];
        $data['warehouse_id'] = $warehouse_id;

        $this->load->view('layouts/header', $data);
        $this->load->view('stock/index', $data);
        $this->load->view('layouts/footer');
    }

    public function adjust($product_id, $warehouse_id)
    {
        $this->_admin_only();

        $product   = $this->product_model->get($product_id);
        $warehouse = $this->warehouse_model->get($warehouse_id);
        if (!$product || !$warehouse) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('adjustment', lang('lbl_adjustment'), 'required|integer');

            if ($this->form_validation->run()) {
                $this->stock_model->adjust($product_id, $warehouse_id, (int) $this->input->post('adjustment'));
                $this->session->set_flashdata('success', lang('msg_updated'));
                redirect('stock');
            }
        }

        $data['page_title'] = lang('stock_adjust');
        $data['product']    = $product;
        $data['warehouse']  = $warehouse;
        $data['stock_row']  = $this->stock_model->get_row($product_id, $warehouse_id);

        $this->load->view('layouts/header', $data);
        $this->load->view('stock/adjust', $data);
        $this->load->view('layouts/footer');
    }

    // Admin shortcut: pick product + warehouse to create a new stock entry
    public function add_entry()
    {
        $this->_admin_only();

        if ($this->input->post()) {
            $pid = (int) $this->input->post('product_id');
            $wid = (int) $this->input->post('warehouse_id');
            if ($pid && $wid) {
                redirect("stock/adjust/{$pid}/{$wid}");
            }
        }

        $data['page_title']  = lang('stock_adjust');
        $data['products']    = $this->product_model->get_active();
        $data['warehouses']  = $this->warehouse_model->all(true);

        $this->load->view('layouts/header', $data);
        $this->load->view('stock/add_entry', $data);
        $this->load->view('layouts/footer');
    }
}