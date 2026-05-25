<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('stock_model');
        $this->load->model('warehouse_model');
    }

    public function low_stock()
    {
        $warehouse_id = $this->user->role === 'user_warehouse'
            ? (int) $this->user->warehouse_id
            : (int) $this->input->get('warehouse_id');

        $search = $this->input->get('search', true) ?: '';

        $data['page_title']   = lang('reports_low_stock');
        $data['rows']         = $this->stock_model->get_low_stock($warehouse_id ?: null, $search);
        $data['warehouses']   = $this->auth_lib->is_admin() ? $this->warehouse_model->all(true) : [];
        $data['warehouse_id'] = $warehouse_id;
        $data['search']       = $search;

        $this->load->view('layouts/header', $data);
        $this->load->view('reports/low_stock', $data);
        $this->load->view('layouts/footer');
    }

    public function low_stock_csv()
    {
        $warehouse_id = $this->user->role === 'user_warehouse'
            ? (int) $this->user->warehouse_id
            : (int) $this->input->get('warehouse_id');

        $search = $this->input->get('search', true) ?: '';
        $rows   = $this->stock_model->get_low_stock($warehouse_id ?: null, $search);

        // Flush CI3's output buffer so we can stream the file directly
        while (ob_get_level()) {
            ob_end_clean();
        }

        $filename = 'low_stock_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $out = fopen('php://output', 'w');

        // UTF-8 BOM — Excel needs this to correctly open files with Arabic text
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            lang('lbl_code'),
            lang('lbl_name'),
            lang('lbl_category'),
            lang('lbl_warehouse'),
            lang('lbl_current_qty'),
            lang('lbl_alert_quantity'),
            lang('lbl_shortage'),
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row->product_code,
                $row->product_name,
                $row->category_name,
                $row->warehouse_name,
                $row->quantity,
                $row->alert_quantity,
                $row->shortage,
            ]);
        }

        fclose($out);
        exit();
    }
}
