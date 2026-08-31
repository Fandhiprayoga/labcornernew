<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStudyProgramAndPhoneToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'study_program_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'username'],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'study_program_id'],
        ]);

        $this->db->query('ALTER TABLE users ADD KEY users_study_program_id (study_program_id)');
        $this->db->query('ALTER TABLE users ADD CONSTRAINT users_study_program_id_foreign FOREIGN KEY (study_program_id) REFERENCES study_programs (id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE users DROP FOREIGN KEY users_study_program_id_foreign');
        $this->forge->dropColumn('users', ['study_program_id', 'phone']);
    }
}
