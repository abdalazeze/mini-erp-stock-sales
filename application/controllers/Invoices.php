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

    public function save()
    {
        if (!$this->input->post()) {
            redirect('invoices/create');
        }

        // Warehouse scope: user_warehouse cannot override their assigned warehouse via POST
        $warehouse_id = $this->user->role === 'user_warehouse'
            ? (int) $this->user->warehouse_id
            : (int) $this->input->post('warehouse_id');

        $raw_lines = $this->input->post('lines') ?: [];
        if (!$raw_lines) {
            $this->session->set_flashdata('error', lang('invoice_no_lines'));
            redirect('invoices/create');
        }

        $is_admin = $this->auth_lib->is_admin();
        $lines    = [];

        foreach ($raw_lines as $line) {
            $pid = (int) ($line['product_id'] ?? 0);
            $qty = (int) ($line['qty'] ?? 0);
            if (!$pid || $qty < 1) {
                continue;
            }

            if ($is_admin) {
                // Admin price: validate positive, max 2 decimal places
                $unit_price = round((float) ($line['unit_price'] ?? 0), 2);
                if ($unit_price <= 0) {
                    continue;
                }
            } else {
                // user_warehouse: force price from product table — ignore posted value
                $product    = $this->product_model->get($pid);
                $unit_price = $product ? round((float) $product->price, 2) : 0;
                if (!$unit_price) {
                    continue;
                }
            }

            $lines[] = ['product_id' => $pid, 'qty' => $qty, 'unit_price' => $unit_price];
        }

        if (!$lines) {
            $this->session->set_flashdata('error', lang('invoice_no_lines'));
            redirect('invoices/create');
        }

        $this->load->library('invoice_service');
        $result = $this->invoice_service->create([
            'customer_id'      => (int) $this->input->post('customer_id'),
            'warehouse_id'     => $warehouse_id,
            'user_id'          => $this->user->id,
            'discount_percent' => (float) $this->input->post('discount_percent'),
            'lines'            => $lines,
        ]);

        if (isset($result['error'])) {
            $this->session->set_flashdata('error', $result['error']);
            redirect('invoices/create');
        }

        $this->session->set_flashdata('success', lang('invoice_saved'));
        redirect('invoices/view/' . $result['invoice_id']);
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
