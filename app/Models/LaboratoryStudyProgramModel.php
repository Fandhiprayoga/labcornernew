<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryStudyProgramModel extends Model
{
    protected $table         = 'laboratory_study_programs';
    protected $primaryKey    = 'laboratory_id';
    protected $returnType    = 'array';
    protected $allowedFields = ['laboratory_id', 'study_program_id'];
}