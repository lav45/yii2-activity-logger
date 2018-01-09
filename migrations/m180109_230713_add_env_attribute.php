<?php

use yii\db\Migration;

/**
 * Class m180109_230713_add_env_attribute
 */
class m180109_230713_add_env_attribute extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%activity_log}}', 'env', $this->string(32));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%activity_log}}', 'env');
    }
}
