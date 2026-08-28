<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class LaboranUserSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();

        $laborans = [
            [
                'kode'     => 'LAB-01',
                'nama'     => 'Ahmad Yogi Kurniawan',
                'gelar'    => 'A.Md.',
                'username' => 'lab-01',
                'email'    => 'lab-01@example.com',
            ],
            [
                'kode'     => 'LAB-02',
                'nama'     => 'Andi Reynaldi',
                'gelar'    => 'S.Ds.',
                'username' => 'lab-02',
                'email'    => 'lab-02@example.com',
            ],
            [
                'kode'     => 'LAB-03',
                'nama'     => 'Anggit Perdana',
                'gelar'    => 'S.T.',
                'username' => 'lab-03',
                'email'    => 'lab-03@example.com',
            ],
            [
                'kode'     => 'LAB-04',
                'nama'     => 'Ardian',
                'gelar'    => 'S.Kom',
                'username' => 'lab-04',
                'email'    => 'lab-04@example.com',
            ],
            [
                'kode'     => 'LAB-05',
                'nama'     => 'Dias Feby Budiarly',
                'gelar'    => 'A.Md.Si.',
                'username' => 'lab-05',
                'email'    => 'lab-05@example.com',
            ],
            [
                'kode'     => 'LAB-06',
                'nama'     => 'Khikmatul Aliyah',
                'gelar'    => 'S.T.',
                'username' => 'lab-06',
                'email'    => 'lab-06@example.com',
            ],
            [
                'kode'     => 'LAB-07',
                'nama'     => 'Khosirun',
                'gelar'    => 'S.Kom',
                'username' => 'lab-07',
                'email'    => 'lab-07@example.com',
            ],
            [
                'kode'     => 'LAB-08',
                'nama'     => 'Presty Wibi Hayomi',
                'gelar'    => 'S.T.',
                'username' => 'lab-08',
                'email'    => 'lab-08@example.com',
            ],
            [
                'kode'     => 'LAB-09',
                'nama'     => 'Toga Diki Andreas',
                'gelar'    => 'S.T.',
                'username' => 'lab-09',
                'email'    => 'lab-09@example.com',
            ],
        ];

        foreach ($laborans as $laboran) {
            $user = $users->findByCredentials(['email' => $laboran['email']])
                ?? $users->findByCredentials(['username' => $laboran['username']]);

            if ($user === null) {
                $user = new User([
                    'username' => $laboran['username'],
                    'email'    => $laboran['email'],
                    'password' => 'password123',
                    'active'   => 1,
                ]);

                $users->save($user);
                $user = $users->findById($users->getInsertID());

                echo "User {$laboran['kode']} - {$laboran['nama']} {$laboran['gelar']} created\n";
            } else {
                echo "User {$laboran['kode']} - {$laboran['nama']} {$laboran['gelar']} already exists, skipped create\n";
            }

            $user->addGroup('laboran');
        }

        echo "\n=== Laboran Login Credentials ===\n";
        echo "Email    : lab-01@example.com s/d lab-09@example.com\n";
        echo "Password : password123\n";
        echo "Role     : laboran\n";
        echo "=================================\n";
    }
}