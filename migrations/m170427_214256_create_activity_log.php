<?php

use yii\db\Migration;

class m170427_214256_create_activity_log extends Migration
{
    private $tableOptions;

    public function init()
    {
        parent::init();
        if ($this->db->driverName === 'mysql') {
            /** @see https://stackoverflow.com/questions/766809 */
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
    }

    public function safeUp()
    {
        $this->createTable('{{%activity_log}}', [
            'id' => $this->bigPrimaryKey(),
            'entity_name' => $this->string(32)->notNull(),
            'entity_id' => $this->string(32),
            'created_at' => $this->integer()->notNull(),
            'user_id' => $this->string(32),
            'user_name' => $this->string(255),
            'action' => $this->string(32),
            'env' => $this->string(32),
            'data' => $this->text(),
        ], $this->tableOptions);

        $this->createIndex('activity_log-entity_name-entity_id-idx', '{{%activity_log}}', ['entity_name', 'entity_id']);
        $this->createIndex('activity_log-user_id', '{{%activity_log}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%activity_log}}');
    }
}
