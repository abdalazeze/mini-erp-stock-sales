<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_model');
    }

    public function index()
    {
        $data['page_title'] = lang('customers_title');
        $data['customers']  = $this->customer_model->all();
        $this->load->view('layouts/header', $data);
        $this->load->view('customers/index', $data);
        $this->load->view('layouts/footer');
    }

    public function add()
    {
        $this->_form();
    }

    public function edit($id)
    {
        $customer = $this->customer_model->get($id);
        if (!$customer) {
            show_404();
        }
        $this->_form($customer);
    }

    public function toggle($id)
    {
        $this->customer_model->toggle_active($id);
        redirect('customers');
    }

    private function _form($customer = null)
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name',  lang('lbl_name'),  'required|trim|max_length[150]');
            $this->form_validation->set_rules('phone', lang('lbl_phone'), 'trim|max_length[30]');

            if ($this->form_validation->run()) {
                $row = [
                    'name'  => $this->input->post('name',  true),
                    'phone' => $this->input->post('phone', true) ?: null,
                ];
                $customer ? $this->customer_model->update($customer->id, $row)
                          : $this->customer_model->insert($row);
                $this->session->set_flashdata('success', lang('msg_saved'));
                redirect('customers');
            }
        }

        $data['page_title'] = lang($customer ? 'customers_edit' : 'customers_add');
        $data['customer']   = $customer;
        $this->load->view('layouts/header', $data);
        $this->load->view('customers/form', $data);
        $this->load->view('layouts/footer');
    }
}
