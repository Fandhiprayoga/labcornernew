<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultyModel extends Model
{
    protected $table          = 'faculties';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['uuid', 'code', 'name', 'dean_name', 'status', 'description'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
}