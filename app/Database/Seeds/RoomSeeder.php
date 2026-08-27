<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            'TT-303',
            'IOT-304',
            'TT-306',
            'IOT Lt. 4',
            'DSP Lt.2',
            'IOT-106',
            'IOT-306',
            'DSP-406',
            'DC-305',
            'IOT-302',
            'IOT-303',
            'DC-303',
            'DC-104',
            'TT-304',
            'TT-305',
            'DSP-104',
            'DSP-102',
            'TT-101',
            'DSP-105',
            'DSP-103',
            'IOT-305',
            'TT-106',
            'DSP-408',
            'TT-301',
            'TT-302',
            'DEPAN DC',
            'DSP Lt1',
            'DSP-407',
        ];

        $now = date('Y-m-d H:i:s');
        $data = [];

        foreach ($rooms as $room) {
            $data[] = [
                'code'       => $room,
                'name'       => $room,
                'capacity'   => 30,
                'type'       => 'laboratorium',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->table('rooms')->ignore(true)->insertBatch($data);
    }
}