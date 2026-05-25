<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoices extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_model');
        $this->load->model('warehouse_model');
        $this->load->model('product_model');
    }

    public function index()
    {
        // Invoice list — commit #13
        redirect('invoices/create');
    }

    public function create()
    {
        $data['page_title'] = lang('invoices_new');
        $data['customers']  = $this->customer_model->all(true);
        $data['warehouses'] = $this->warehouse_model->all(true);
        $data['is_admin']   = $this->auth_lib->is_admin();
        $data['extra_js']   = 'assets/js/invoice.js';

        $this->load->view('layouts/header', $data);
        $this->load->view('invoices/create', $data);
        $this->load->view('layouts/footer');
    }

    public function search_product()
    {
        $q = trim($this->input->get('q', true));
        if (strlen($q) < 2) {
            $this->output->set_content_type('application/json')->set_output('[]');
            return;
        }
        $results = $this->product_model->search($q);
        $this->output->set_content_type('application/json')->set_output(json_encode($results));
    }
}
