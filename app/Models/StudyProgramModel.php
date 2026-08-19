<?php

namespace App\Models;

use CodeIgniter\Model;

class StudyProgramModel extends Model
{
    protected $table          = 'study_programs';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['uuid', 'faculty_id', 'code', 'name', 'degree', 'status', 'description'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
}