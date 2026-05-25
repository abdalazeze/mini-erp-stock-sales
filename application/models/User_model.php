    <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function get_by_username($username)
    {
        return $this->db->get_where('users', ['username' => $username, 'is_active' => 1])->row();
    }
}