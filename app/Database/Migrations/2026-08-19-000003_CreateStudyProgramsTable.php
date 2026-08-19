<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudyProgramsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'        => ['type' => 'CHAR', 'constraint' => 36],
            'faculty_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'degree'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('faculty_id');
        $this->forge->addForeignKey('faculty_id', 'faculties', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('study_programs');
    }

    public function down()
    {
        $this->forge->dropTable('study_programs', true);
    }
}