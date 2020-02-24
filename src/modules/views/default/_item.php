<?php
/**
 * @var $this yii\web\View
 * @var $model lav45\activityLogger\modules\models\ActivityLogViewModel
 * @var $key string
 * @var $index integer
 * @var $widget yii\widgets\ListView
 */

$formatter = Yii::$app->getFormatter();
$actionList = $model->getActionList();

?>
<h4>
    <?= $model->getEntityName() ?>

    <?= $model->getUserName() . ' ' . $actionList[$model->action] ?>

    <span title="<?= $formatter->asDatetime($model->created_at) ?>">
        <?= $formatter->asRelativeTime($model->created_at) ?>
    </span>

    <?php if ($model->env): ?>
        <small class="pull-right"><?= $model->getEnv() ?></small>
    <?php endif; ?>
</h4>
<ul class="details">
    <?php foreach ($model->getData() as $attribute => $values): ?>
        <?php if (is_string($values)): ?>
            <li>
                <?php if(is_numeric($attribute) || empty($attribute)): ?>
                    <?= $values ?>
                <?php else: ?>
                    <strong><?= $attribute ?></strong> <?= $values ?>
                <?php endif; ?>
            </li>
        <?php else: ?>
            <li>
                <?= Yii::t('lav45/logger', '<strong>{attribute}</strong> has been changed', ['attribute' => $attribute]) ?>

                <?= Yii::t('lav45/logger', 'from') ?>
                <strong><i class="details-text"><?= $values->getOldValue() ?></i></strong>

                <?= Yii::t('lav45/logger', 'to') ?>
                <strong><i class="details-text"><?= $values->getNewValue() ?></i></strong>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
