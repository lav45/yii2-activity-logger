<?php

use yii\db\Migration;

/**
 * Class m200211_093621_update_log_data
 */
class m200211_093621_update_log_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('activity_log', ['action' => 'create'], ['action' => 'created']); // ActiveLogBehavior::ACTION_CREATE
        $this->update('activity_log', ['action' => 'update'], ['action' => 'updated']); // ActiveLogBehavior::ACTION_UPDATE
        $this->update('activity_log', ['action' => 'delete'], ['action' => 'removed']); // ActiveLogBehavior::ACTION_DELETE
    }
}
