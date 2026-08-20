<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryLaboranModel extends Model
{
    protected $table         = 'laboratory_laborans';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'laboratory_id'];
    protected $useTimestamps = true;
}