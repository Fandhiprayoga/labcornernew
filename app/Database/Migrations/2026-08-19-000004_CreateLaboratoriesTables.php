<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoriesTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'room_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('room_id');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('laboratories');

        $this->forge->addField([
            'laboratory_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'study_program_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey(['laboratory_id', 'study_program_id'], true);
        $this->forge->addKey('study_program_id');
        $this->forge->addForeignKey('laboratory_id', 'laboratories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('study_program_id', 'study_programs', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('laboratory_study_programs');
    }

    public function down()
    {
        $this->forge->dropTable('laboratory_study_programs', true);
        $this->forge->dropTable('laboratories', true);
    }
}