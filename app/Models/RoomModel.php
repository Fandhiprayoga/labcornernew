<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table          = 'rooms';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['code', 'name', 'building', 'floor', 'capacity', 'type', 'status', 'description'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
}