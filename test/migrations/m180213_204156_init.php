<?php

use yii\db\Migration;

class m180213_204156_init extends Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'login' => $this->string(),
            'is_hidden' => $this->boolean()->defaultValue(false)->notNull(),
            'friend_count' => $this->integer(),
            'salary' => $this->float(),
            'birthday' => $this->date(),
            'status' => $this->integer()->defaultValue(10)->notNull(),
            'company_id' => $this->integer(),
            '_array_status' => $this->text(),
            'fail_relation' => $this->integer(),
            'fail_link' => $this->integer(),
        ]);

        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);

        $this->insert('company', ['name' => 'Asus']);
        $this->insert('company', ['name' => 'HP']);

        $this->createTable('test_entity_name', [
            'name' => $this->string(),
        ]);
    }

    public function down()
    {
        $this->dropTable('user');
        $this->dropTable('company');
        $this->dropTable('test_entity_name');
    }
}