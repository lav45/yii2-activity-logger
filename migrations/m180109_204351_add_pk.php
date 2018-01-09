<?php

use yii\db\Migration;

/**
 * Class m180109_204351_add_pk
 */
class m180109_204351_add_pk extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%activity_log}}', 'id', $this->bigPrimaryKey());

        $this->dropIndex('activity_log-created_at', '{{%activity_log}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createIndex('activity_log-created_at', '{{%activity_log}}', 'created_at');

        $this->dropColumn('{{%activity_log}}', 'id');
    }
}
