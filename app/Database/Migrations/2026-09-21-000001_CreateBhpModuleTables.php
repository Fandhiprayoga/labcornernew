<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBhpModuleTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'nama_periode' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tanggal_mulai' => ['type' => 'DATETIME'],
            'tanggal_selesai' => ['type' => 'DATETIME'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey(['tanggal_mulai', 'tanggal_selesai']);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('periode_pengajuan');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'kode_pengajuan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'periode_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'laboran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'laboratory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'study_program_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nama_lab_snapshot' => ['type' => 'VARCHAR', 'constraint' => 100],
            'prodi_snapshot' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'grand_total_estimasi' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'DRAFT'],
            'catatan_revisi' => ['type' => 'TEXT', 'null' => true],
            'alasan_penolakan' => ['type' => 'TEXT', 'null' => true],
            'nominal_cair' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'tanggal_cair' => ['type' => 'DATE', 'null' => true],
            'tanggal_belanja' => ['type' => 'DATE', 'null' => true],
            'realisasi_biaya' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'catatan_pembelian' => ['type' => 'TEXT', 'null' => true],
            'catatan_verifikasi' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('kode_pengajuan');
        $this->forge->addKey(['periode_id', 'laboratory_id']);
        $this->forge->addKey('laboran_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('periode_id', 'periode_pengajuan', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('laboran_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('laboratory_id', 'laboratories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('study_program_id', 'study_programs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'pengajuan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 255],
            'spesifikasi' => ['type' => 'TEXT', 'null' => true],
            'qty' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'harga_satuan' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'total_harga' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'vendor' => ['type' => 'VARCHAR', 'constraint' => 150],
            'link_toko_online' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('pengajuan_id');
        $this->forge->addForeignKey('pengajuan_id', 'pengajuan_bhp', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp_item');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'pengajuan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_file' => ['type' => 'VARCHAR', 'constraint' => 30],
            'file_path' => ['type' => 'TEXT'],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'uploaded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'uploaded_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey(['pengajuan_id', 'tipe_file']);
        $this->forge->addForeignKey('pengajuan_id', 'pengajuan_bhp', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp_eviden');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'pengajuan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'changed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'note' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('pengajuan_id');
        $this->forge->addForeignKey('pengajuan_id', 'pengajuan_bhp', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pengajuan_bhp_status_history');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'admin_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'target_entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'notes' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey(['action_type', 'target_entity_id']);
        $this->forge->addForeignKey('admin_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('audit_log_admin');
    }

    public function down()
    {
        foreach (['audit_log_admin', 'pengajuan_bhp_status_history', 'pengajuan_bhp_eviden', 'pengajuan_bhp_item', 'pengajuan_bhp', 'periode_pengajuan'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
