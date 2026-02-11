<?php

namespace App\Models;

use CodeIgniter\Model;

class WeeklyworkModel extends Model
{
    protected $table = 'aa_weeklywork';
    protected $primaryKey = 'ww_id';
    protected $allowedFields = ['ww_u_id', 'ww_p_id', 'ww_date', 'ww_hours', 'ww_description'];

    protected $useTimestamps = false;
}
