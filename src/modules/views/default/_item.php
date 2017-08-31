<?php
/**
 * @var $this yii\web\View
 * @var $model lav45\activityLogger\modules\models\ActivityLogViewModel
 * @var $key string
 * @var $index integer
 * @var $widget yii\widgets\ListView
 */

$formatter = Yii::$app->formatter;

?>
<h4>
    <?= $model->getUserName() . ' ' . Yii::t('app', $model->action) ?>

    <span title="<?= $formatter->asDatetime($model->created_at) ?>">
        <?= $formatter->asRelativeTime($model->created_at) ?>
    </span>
</h4>
<ul class="details">
    <?php foreach ($model->getData() as $attribute => $values): ?>
        <?php if (is_int($attribute)): ?>
            <li><?= $values; ?></li>
        <?php else: ?>
            <li>
                <?= Yii::t('app', 'The setting <strong>{attribute}</strong> has ben changed', ['attribute' => $attribute]) ?>

                <?= Yii::t('app', 'from'); ?>
                <strong><i><?= $values->getOldValue(); ?></i></strong>

                <?= Yii::t('app', 'to'); ?>
                <strong><i><?= $values->getNewValue(); ?></i></strong>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
