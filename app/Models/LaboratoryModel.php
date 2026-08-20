<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryModel extends Model
{
    protected $table          = 'laboratories';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['room_id', 'name', 'status', 'description'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
}