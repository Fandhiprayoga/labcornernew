<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LaboratoryLaboranSeeder extends Seeder
{
    public function run()
    {
        $this->call(LaboranUserSeeder::class);
        $this->call(LaboratorySeeder::class);

        $now = date('Y-m-d H:i:s');

        $assignments = [
            'lab-01@example.com' => [
                'Programming Laboratory',
                'Communication System Laboratory',
                'NetSec Laboratory',
            ],
            'lab-02@example.com' => [
                'VSAT and Antena Propagation Laboratory',
                'Internet of Everything (IoE) Laboratory',
                'Electronic Laboratory',
            ],
            'lab-03@example.com' => [
                'Gallery Satria',
                'DKV Workshop',
                'Audio Visual Laboratory',
            ],
            'lab-04@example.com' => [
                'Graphic Computer Laboratory',
                'Digital Creactive Laboratory',
                'Integrated Industrial Laboratory',
            ],
            'lab-05@example.com' => [
                'Laboratorium Jaringan Komputer',
                'Laboratorium Aplikasi dan Multimedia',
                'Laboratorium High Performance',
            ],
            'lab-06@example.com' => [
                'Laboratorium Programming',
                'Laboratorium Data Sains',
                'Logistic System Simulation Laboratory',
            ],
            'lab-07@example.com' => [
                'Physics Laboratory',
                'Instrument Laboratory',
                'Control System Laboratory',
            ],
            'lab-08@example.com' => [
                'Basics Science Laboratory',
                'Microbiology Laboratory',
                'Food Processing Engineering',
            ],
            'lab-09@example.com' => [
                'Integrated Logistic Laboratory',
                'Ergonomy Laboratory',
                'Industry Simulation Laboratory',
                'Despro Workshop',
            ],
        ];

        foreach ($assignments as $email => $laboratoryNames) {
            $identity = $this->db->table('auth_identities')
                ->select('user_id')
                ->where('type', 'email_password')
                ->where('secret', $email)
                ->get()
                ->getRowArray();

            if (! $identity) {
                echo "Laboran {$email} not found, skipped\n";
                continue;
            }

            foreach ($laboratoryNames as $laboratoryName) {
                $laboratory = $this->db->table('laboratories')
                    ->select('id')
                    ->where('name', $laboratoryName)
                    ->get()
                    ->getRowArray();

                if (! $laboratory) {
                    echo "Laboratory {$laboratoryName} not found, skipped\n";
                    continue;
                }

                $this->db->table('laboratory_laborans')->ignore(true)->insert([
                    'user_id'       => $identity['user_id'],
                    'laboratory_id' => $laboratory['id'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        echo "Master data laboran seeded successfully\n";
    }
}