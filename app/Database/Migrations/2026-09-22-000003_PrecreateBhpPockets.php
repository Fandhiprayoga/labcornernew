<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PrecreateBhpPockets extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE pengajuan_bhp MODIFY laboran_id INT(11) UNSIGNED NULL, MODIFY laboratory_id INT(11) UNSIGNED NULL');
        $this->db->query("UPDATE pengajuan_bhp SET nama_lab_snapshot = 'Multi laboratorium' WHERE nama_lab_snapshot IS NULL OR nama_lab_snapshot = ''");
    }

    public function down()
    {
        $this->db->query("UPDATE pengajuan_bhp SET nama_lab_snapshot = 'Multi laboratorium' WHERE nama_lab_snapshot IS NULL OR nama_lab_snapshot = ''");
        $this->db->query('ALTER TABLE pengajuan_bhp MODIFY laboran_id INT(11) UNSIGNED NOT NULL, MODIFY laboratory_id INT(11) UNSIGNED NOT NULL');
    }
}