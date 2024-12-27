<?php
/**
 * @var $this yii\web\View
 * @var $model lav45\activityLogger\modules\models\ActivityLogViewModel
 * @var $key string
 * @var $index integer
 * @var $widget yii\widgets\ListView
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<h4>
    [
    <?= Html::a(Html::encode($model->entity_name), Url::current([
        'entityName' => $model->entity_name,
        'entityId' => null,
        'page' => null
    ])) ?>
    <?php if ($model->entity_id): ?>
        <?= ':' . Html::a(Html::encode($model->entity_id), Url::current([
            'entityName' => $model->entity_name,
            'entityId' => $model->entity_id,
            'page' => null
        ])) ?>
    <?php endif; ?>
    ]

    <?php
    $url = Url::current(['userId' => $model->user_id, 'page' => null]);
    $action = Yii::t('lav45/logger', $model->action);
    ?>
    <?= Html::a(Html::encode($model->user_name), $url) . ' ' . Html::encode($action) ?>

    <span><?= Yii::$app->getFormatter()->asDatetime($model->created_at) ?></span>

    <?php if ($model->env): ?>
        <small style="float: right;">
            <?php $url = Url::current(['env' => $model->env, 'page' => null]); ?>
            <?= Html::a(Html::encode($model->env), $url) ?>
        </small>
    <?php endif; ?>
</h4>
<ul class="details">
    <?php foreach ($model->getData() as $attribute => $values): ?>
        <?php if (is_string($values)): ?>
            <li>
                <?php if (is_string($attribute)): ?>
                    <strong><?= Html::encode($attribute) ?></strong>
                <?php endif; ?>
                <?= Html::encode(Yii::t('lav45/logger', $values)) ?>
            </li>
        <?php else: ?>
            <li>
                <?= Yii::t('lav45/logger', '<strong>{attribute}</strong> has been changed', ['attribute' => Html::encode($attribute)]) ?>

                <?= Html::encode(Yii::t('lav45/logger', 'from')) ?>
                <strong><i class="details-text"><?= $values->getOldValue() ?></i></strong>

                <?= Html::encode(Yii::t('lav45/logger', 'to')) ?>
                <strong><i class="details-text"><?= $values->getNewValue() ?></i></strong>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
