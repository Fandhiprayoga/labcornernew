<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run()
    {
        helper('uuid');

        $this->call(RoomSeeder::class);
        $this->call(FacultySeeder::class);
        $this->call(StudyProgramSeeder::class);

        $now = date('Y-m-d H:i:s');

        // Map program studi abbreviation (as referenced by lab data) to study_programs.code
        $studyProgramCodeMap = [
            'S1 TT'      => 'PS-01',
            'D3 TT'      => 'PS-02',
            'S1 DKV'     => 'PS-03',
            'S1 TI'      => 'PS-04',
            'S1 TL'      => 'PS-05',
            'S1 Bisig'   => 'PS-06',
            'S1 SI'      => 'PS-07',
            'S1 IF'      => 'PS-08',
            'S1 RPL'     => 'PS-09',
            'Data Sains' => 'PS-10',
            'S1 TE'      => 'PS-11',
            'S1 biomedis'=> 'PS-12',
            'S1 Biomedis'=> 'PS-12',
            'S1 TP'      => 'PS-13',
            'S1 DP'      => 'PS-14',
        ];

        $laboratories = [
            ['name' => 'Programming Laboratory', 'room' => 'TT-303', 'programs' => ['S1 TT', 'D3 TT']],
            ['name' => 'Communication System Laboratory', 'room' => 'IOT-304', 'programs' => ['S1 TT', 'D3 TT']],
            ['name' => 'NetSec Laboratory', 'room' => 'TT-306', 'programs' => ['S1 TT', 'D3 TT']],
            ['name' => 'VSAT and Antena Propagation Laboratory', 'room' => 'IOT Lt. 4', 'programs' => ['S1 TT', 'D3 TT']],
            ['name' => 'Gallery Satria', 'room' => 'DSP Lt.2', 'programs' => ['S1 DKV']],
            ['name' => 'DKV Workshop', 'room' => 'IOT-106', 'programs' => ['S1 DKV']],
            ['name' => 'Audio Visual Laboratory', 'room' => 'IOT-306', 'programs' => ['S1 DKV']],
            ['name' => 'Graphic Computer Laboratory', 'room' => 'DSP-406', 'programs' => ['S1 TI', 'S1 TL', 'S1 Bisig']],
            ['name' => 'Laboratorium Jaringan Komputer', 'room' => 'DC-305', 'programs' => ['S1 SI']],
            ['name' => 'Laboratorium Aplikasi dan Multimedia', 'room' => 'IOT-302', 'programs' => ['S1 IF']],
            ['name' => 'Laboratorium High Performance', 'room' => 'IOT-303', 'programs' => ['S1 IF']],
            ['name' => 'Laboratorium Programming', 'room' => 'DC-303', 'programs' => ['S1 RPL']],
            ['name' => 'Laboratorium Data Sains', 'room' => 'DC-104', 'programs' => ['Data Sains']],
            ['name' => 'Physics Laboratory', 'room' => 'TT-304', 'programs' => ['S1 TT', 'S1 TE', 'S1 biomedis', 'S1 TP', 'S1 TI', 'S1 TL']],
            ['name' => 'Instrument Laboratory', 'room' => 'TT-305', 'programs' => ['S1 TT', 'S1 TE', 'S1 biomedis', 'S1 TP', 'S1 TI', 'S1 TL']],
            ['name' => 'Basics Science Laboratory', 'room' => 'DSP-104', 'programs' => ['S1 TP', 'S1 Biomedis']],
            ['name' => 'Microbiology Laboratory', 'room' => 'DSP-102', 'programs' => ['S1 TP', 'S1 Biomedis']],
            ['name' => 'Food Processing Engineering', 'room' => 'TT-101', 'programs' => ['S1 TP', 'S1 Biomedis']],
            ['name' => 'Integrated Logistic Laboratory', 'room' => 'DSP-105', 'programs' => ['S1 TI', 'S1 TL']],
            ['name' => 'Ergonomy Laboratory', 'room' => 'DSP-103', 'programs' => ['S1 TI', 'S1 TL']],
            ['name' => 'Industry Simulation Laboratory', 'room' => 'IOT-305', 'programs' => ['S1 TI', 'S1 TL']],
            ['name' => 'Control System Laboratory', 'room' => 'TT-106', 'programs' => ['S1 Biomedis', 'S1 TE', 'D3 TT']],
            ['name' => 'Logistic System Simulation Laboratory', 'room' => 'DSP-408', 'programs' => ['S1 TI', 'S1 TL', 'S1 Bisig']],
            ['name' => 'Electronic Laboratory', 'room' => 'TT-301', 'programs' => ['S1 TE', 'S1 TT', 'D3 TT']],
            ['name' => 'Internet of Everything (IoE) Laboratory', 'room' => 'TT-302', 'programs' => ['S1 TT', 'D3 TT']],
            ['name' => 'Integrated Industrial Laboratory', 'room' => 'DEPAN DC', 'programs' => ['S1 DP', 'S1 TI']],
            ['name' => 'Despro Workshop', 'room' => 'DSP Lt1', 'programs' => ['S1 DP', 'S1 TI']],
            ['name' => 'Digital Creactive Laboratory', 'room' => 'DSP-407', 'programs' => ['S1 TI', 'S1 TL', 'S1 Bisig']],
        ];

        foreach ($laboratories as $laboratory) {
            $room = $this->db->table('rooms')->where('code', $laboratory['room'])->get()->getRowArray();

            if (! $room) {
                continue;
            }

            $existing = $this->db->table('laboratories')
                ->where('room_id', $room['id'])
                ->where('name', $laboratory['name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $laboratoryId = $existing['id'];
            } else {
                $this->db->table('laboratories')->insert([
                    'uuid'       => generate_uuid(),
                    'room_id'    => $room['id'],
                    'name'       => $laboratory['name'],
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $laboratoryId = $this->db->insertID();
            }

            foreach ($laboratory['programs'] as $programAbbr) {
                $code = $studyProgramCodeMap[$programAbbr] ?? null;

                if (! $code) {
                    continue;
                }

                $studyProgram = $this->db->table('study_programs')->where('code', $code)->get()->getRowArray();

                if (! $studyProgram) {
                    continue;
                }

                $this->db->table('laboratory_study_programs')->ignore(true)->insert([
                    'laboratory_id'    => $laboratoryId,
                    'study_program_id' => $studyProgram['id'],
                ]);
            }
        }
    }
}
