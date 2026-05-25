<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function current_lang()
{
    $CI =& get_instance();
    return $CI->session->userdata('lang') ?: 'ar';
}