<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouses extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('warehouse_model');
    }

    public function index()
    {
        $data['page_title']  = lang('warehouses_title');
        $data['warehouses']  = $this->warehouse_model->all();
        $this->load->view('layouts/header', $data);
        $this->load->view('warehouses/index', $data);
        $this->load->view('layouts/footer');
    }

    public function add()
    {
        $this->_form();
    }

    public function edit($id)
    {
        $warehouse = $this->warehouse_model->get($id);
        if (!$warehouse) {
            show_404();
        }
        $this->_form($warehouse);
    }

    public function toggle($id)
    {
        $this->warehouse_model->toggle_active($id);
        redirect('warehouses');
    }

    private function _form($warehouse = null)
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', lang('lbl_name'), 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', lang('lbl_code'), 'required|trim|max_length[20]');

            if ($this->form_validation->run()) {
                $code = strtoupper($this->input->post('code', true));

                if ($this->warehouse_model->code_exists($code, $warehouse ? $warehouse->id : null)) {
                    $this->session->set_flashdata('error', 'Warehouse code already in use.');
                } else {
                    $row = [
                        'name' => $this->input->post('name', true),
                        'code' => $code,
                    ];
                    $warehouse ? $this->warehouse_model->update($warehouse->id, $row)
                               : $this->warehouse_model->insert($row);
                    $this->session->set_flashdata('success', lang('msg_saved'));
                    redirect('warehouses');
                }
            }
        }

        $data['page_title'] = lang($warehouse ? 'warehouses_edit' : 'warehouses_add');
        $data['warehouse']  = $warehouse;
        $this->load->view('layouts/header', $data);
        $this->load->view('warehouses/form', $data);
        $this->load->view('layouts/footer');
    }
}
