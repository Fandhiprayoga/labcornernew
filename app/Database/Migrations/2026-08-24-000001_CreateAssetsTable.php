<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'              => ['type' => 'CHAR', 'constraint' => 36],
            'asset_code'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 150],
            'laboratory_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'category'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'brand'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'model'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'serial_number'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'acquisition_date'  => ['type' => 'DATE', 'null' => true],
            'purchase_price'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'can_be_borrowed'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'status'            => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'ready'],
            'description'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('asset_code');
        $this->forge->addKey('laboratory_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('laboratory_id', 'laboratories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('assets');
    }

    public function down()
    {
        $this->forge->dropTable('assets', true);
    }
}