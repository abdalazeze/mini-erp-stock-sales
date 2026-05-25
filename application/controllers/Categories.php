<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('category_model');
    }

    public function index()
    {
        $data['page_title']  = lang('categories_title');
        $data['categories']  = $this->category_model->all();
        $this->load->view('layouts/header', $data);
        $this->load->view('categories/index', $data);
        $this->load->view('layouts/footer');
    }

    public function add()
    {
        $this->_form();
    }

    public function edit($id)
    {
        $category = $this->category_model->get($id);
        if (!$category) {
            show_404();
        }
        $this->_form($category);
    }

    public function toggle($id)
    {
        $this->category_model->toggle_active($id);
        redirect('categories');
    }

    private function _form($category = null)
    {
        $this->form_validation->set_rules('name', lang('lbl_name'), 'required|trim|max_length[100]');

        if ($this->form_validation->run()) {
            $data = ['name' => $this->input->post('name', true)];
            if ($category) {
                $this->category_model->update($category->id, $data);
            } else {
                $this->category_model->insert($data);
            }
            $this->session->set_flashdata('success', lang('msg_saved'));
            redirect('categories');
        }

        $data['page_title'] = $category ? lang('categories_edit') : lang('categories_add');
        $data['category']   = $category;
        $this->load->view('layouts/header', $data);
        $this->load->view('categories/form', $data);
        $this->load->view('layouts/footer');
    }
}