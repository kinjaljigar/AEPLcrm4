<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectMessageModel extends Model
{
    protected $table = 'aa_project_messages';
    protected $primaryKey = 'pm_id';
    protected $allowedFields = ['pm_p_id', 'pm_u_id', 'pm_message', 'pm_created'];

    protected $useTimestamps = false;
}
