<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    protected $casts = [
        'id'                => '?integer',
        'active'            => 'int-bool',
        'permissions'       => 'array',
        'groups'            => 'array',
        'study_program_id'  => '?integer',
    ];
}
