<?php

namespace App\Models;

use App\Entities\User;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $returnType = User::class;

    protected $allowedFields = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'study_program_id',
        'phone',
    ];
}
