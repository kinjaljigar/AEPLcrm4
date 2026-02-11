<?php

namespace App\Models;

use CodeIgniter\Model;

class DependencyModel extends Model
{
    protected $table = 'aa_dependencies';
    protected $primaryKey = 'd_id';
    protected $allowedFields = ['d_p_id', 'd_u_id', 'd_description', 'd_status', 'd_created'];

    protected $useTimestamps = false;
}
