<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run()
    {
        helper('uuid');

        $this->call(LaboratorySeeder::class);

        $now = date('Y-m-d H:i:s');
        $assets = [
            ['code' => 'AST-PRG-001', 'name' => 'PC Workstation Programming', 'laboratory' => 'Programming Laboratory', 'category' => 'Komputer', 'brand' => 'Dell', 'model' => 'OptiPlex 7010', 'serial_number' => 'PRG-001', 'acquisition_date' => '2024-01-15', 'purchase_price' => 12500000, 'can_be_borrowed' => 0, 'status' => 'ready', 'description' => 'Komputer praktikum pemrograman.'],
            ['code' => 'AST-COM-001', 'name' => 'Spectrum Analyzer', 'laboratory' => 'Communication System Laboratory', 'category' => 'Alat Ukur', 'brand' => 'Rigol', 'model' => 'DSA815-TG', 'serial_number' => 'COM-001', 'acquisition_date' => '2023-08-10', 'purchase_price' => 18900000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Alat analisis spektrum untuk praktikum sistem komunikasi.'],
            ['code' => 'AST-NET-001', 'name' => 'Managed Network Switch', 'laboratory' => 'NetSec Laboratory', 'category' => 'Perangkat Jaringan', 'brand' => 'Cisco', 'model' => 'CBS350-24T-4G', 'serial_number' => 'NET-001', 'acquisition_date' => '2024-02-20', 'purchase_price' => 8500000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Switch managed untuk praktikum keamanan jaringan.'],
            ['code' => 'AST-VSA-001', 'name' => 'VSAT Training Kit', 'laboratory' => 'VSAT and Antena Propagation Laboratory', 'category' => 'Peralatan Praktikum', 'brand' => 'SatLab', 'model' => 'VSAT Trainer', 'serial_number' => 'VSA-001', 'acquisition_date' => '2022-07-12', 'purchase_price' => 35000000, 'can_be_borrowed' => 0, 'status' => 'maintenance', 'description' => 'Kit pelatihan VSAT dan propagasi antena.'],
            ['code' => 'AST-DKV-001', 'name' => 'Kamera Mirrorless', 'laboratory' => 'Audio Visual Laboratory', 'category' => 'Peralatan Audio Visual', 'brand' => 'Sony', 'model' => 'Alpha A6400', 'serial_number' => 'DKV-001', 'acquisition_date' => '2023-03-18', 'purchase_price' => 14500000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Kamera untuk produksi konten audio visual.'],
            ['code' => 'AST-GRA-001', 'name' => 'Pen Display Tablet', 'laboratory' => 'Graphic Computer Laboratory', 'category' => 'Perangkat Desain', 'brand' => 'Wacom', 'model' => 'Cintiq 16', 'serial_number' => 'GRA-001', 'acquisition_date' => '2024-04-05', 'purchase_price' => 9800000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Pen display untuk praktikum desain grafis.'],
            ['code' => 'AST-JAR-001', 'name' => 'Router Wireless', 'laboratory' => 'Laboratorium Jaringan Komputer', 'category' => 'Perangkat Jaringan', 'brand' => 'MikroTik', 'model' => 'RB4011iGS+RM', 'serial_number' => 'JAR-001', 'acquisition_date' => '2023-10-11', 'purchase_price' => 4200000, 'can_be_borrowed' => 1, 'status' => 'borrowed', 'description' => 'Router untuk praktikum konfigurasi jaringan.'],
            ['code' => 'AST-DAT-001', 'name' => 'GPU Computing Server', 'laboratory' => 'Laboratorium Data Sains', 'category' => 'Server', 'brand' => 'NVIDIA', 'model' => 'DGX Station', 'serial_number' => 'DAT-001', 'acquisition_date' => '2024-01-30', 'purchase_price' => 75000000, 'can_be_borrowed' => 0, 'status' => 'ready', 'description' => 'Server komputasi untuk pengolahan data dan machine learning.'],
            ['code' => 'AST-FIS-001', 'name' => 'Digital Oscilloscope', 'laboratory' => 'Physics Laboratory', 'category' => 'Alat Ukur', 'brand' => 'Tektronix', 'model' => 'TBS1102B', 'serial_number' => 'FIS-001', 'acquisition_date' => '2022-09-15', 'purchase_price' => 7800000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Osiloskop digital untuk praktikum fisika.'],
            ['code' => 'AST-MIC-001', 'name' => 'Mikroskop Digital', 'laboratory' => 'Microbiology Laboratory', 'category' => 'Peralatan Laboratorium', 'brand' => 'Optika', 'model' => 'B-290TB', 'serial_number' => 'MIC-001', 'acquisition_date' => '2023-06-22', 'purchase_price' => 11200000, 'can_be_borrowed' => 0, 'status' => 'ready', 'description' => 'Mikroskop digital untuk pengamatan mikrobiologi.'],
            ['code' => 'AST-ELK-001', 'name' => 'Digital Multimeter', 'laboratory' => 'Electronic Laboratory', 'category' => 'Alat Ukur', 'brand' => 'Fluke', 'model' => '117', 'serial_number' => 'ELK-001', 'acquisition_date' => '2023-11-08', 'purchase_price' => 3200000, 'can_be_borrowed' => 1, 'status' => 'damaged', 'description' => 'Multimeter digital untuk praktikum elektronika.'],
            ['code' => 'AST-IOE-001', 'name' => 'IoT Development Kit', 'laboratory' => 'Internet of Everything (IoE) Laboratory', 'category' => 'Peralatan Praktikum', 'brand' => 'Arduino', 'model' => 'MKR WiFi 1010', 'serial_number' => 'IOE-001', 'acquisition_date' => '2024-05-17', 'purchase_price' => 1850000, 'can_be_borrowed' => 1, 'status' => 'ready', 'description' => 'Kit pengembangan Internet of Things.'],
        ];

        foreach ($assets as $asset) {
            $laboratory = $this->db->table('laboratories')
                ->select('id')
                ->where('name', $asset['laboratory'])
                ->get()
                ->getRowArray();

            if (! $laboratory) {
                echo "Laboratory {$asset['laboratory']} not found, skipped\n";
                continue;
            }

            unset($asset['laboratory']);

            $this->db->table('assets')->ignore(true)->insert([
                'uuid'            => generate_uuid(),
                'asset_code'      => $asset['code'],
                'name'            => $asset['name'],
                'laboratory_id'   => $laboratory['id'],
                'category'        => $asset['category'],
                'brand'           => $asset['brand'],
                'model'           => $asset['model'],
                'serial_number'   => $asset['serial_number'],
                'acquisition_date'=> $asset['acquisition_date'],
                'purchase_price'  => $asset['purchase_price'],
                'can_be_borrowed' => $asset['can_be_borrowed'],
                'status'          => $asset['status'],
                'description'     => $asset['description'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        echo "Master data asset seeded successfully\n";
    }
}
