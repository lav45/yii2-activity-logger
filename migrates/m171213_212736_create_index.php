<?php

use yii\db\Migration;

/**
 * Class m171213_212736_create_index
 */
class m171213_212736_create_index extends Migration
{
    public function safeUp()
    {
        $this->createIndex('activity_log-user_id', '{{%activity_log}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropIndex('activity_log-user_id', '{{%activity_log}}');
    }
}
