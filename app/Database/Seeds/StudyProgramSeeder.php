<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    public function run()
    {
        helper('uuid');

        $now = date('Y-m-d H:i:s');

        $faculty = $this->db->table('faculties')->where('code', 'FAK-01')->get()->getRowArray();

        if (! $faculty) {
            return;
        }

        $studyPrograms = [
            ['code' => 'PS-01', 'name' => 'S1 TEKNIK TELEKOMUNIKASI', 'degree' => 'S1'],
            ['code' => 'PS-02', 'name' => 'D3 TEKNIK TELEKOMUNIKASI', 'degree' => 'D3'],
            ['code' => 'PS-03', 'name' => 'S1 DESAIN KOMUNIKASI VISUAL', 'degree' => 'S1'],
            ['code' => 'PS-04', 'name' => 'S1 TEKNIK INDUSTRI', 'degree' => 'S1'],
            ['code' => 'PS-05', 'name' => 'S1 TEKNIK LOGISTIK', 'degree' => 'S1'],
            ['code' => 'PS-06', 'name' => 'S1 BISNIS DIGITAL', 'degree' => 'S1'],
            ['code' => 'PS-07', 'name' => 'S1 SISTEM INFORMASI', 'degree' => 'S1'],
            ['code' => 'PS-08', 'name' => 'S1 INFORMATIKA', 'degree' => 'S1'],
            ['code' => 'PS-09', 'name' => 'S1 REKAYASA PERANGKAT LUNAK', 'degree' => 'S1'],
            ['code' => 'PS-10', 'name' => 'S1 SAINS DATA', 'degree' => 'S1'],
            ['code' => 'PS-11', 'name' => 'S1 TEKNIK ELEKTRO', 'degree' => 'S1'],
            ['code' => 'PS-12', 'name' => 'S1 BIOMEDIS', 'degree' => 'S1'],
            ['code' => 'PS-13', 'name' => 'S1 TEKNIK PANGAN', 'degree' => 'S1'],
            ['code' => 'PS-14', 'name' => 'S1 DESAIN PRODUK', 'degree' => 'S1'],
        ];

        foreach ($studyPrograms as $studyProgram) {
            $this->db->table('study_programs')->ignore(true)->insert([
                'uuid'       => generate_uuid(),
                'faculty_id' => $faculty['id'],
                'code'       => $studyProgram['code'],
                'name'       => $studyProgram['name'],
                'degree'     => $studyProgram['degree'],
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
