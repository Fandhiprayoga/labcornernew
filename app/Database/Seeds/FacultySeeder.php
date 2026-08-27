<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('faculties')->ignore(true)->insert([
            'uuid'       => '9be7b85d-8498-487f-92e1-99ea38083dc5',
            'code'       => 'FAK-01',
            'name'       => 'TEKOM UNIVERSITAS KAMPUS PURWOKERTO',
            'status'     => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}