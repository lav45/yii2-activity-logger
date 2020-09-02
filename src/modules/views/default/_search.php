<?php
/**
 * @var $this yii\web\View
 * @var $model lav45\activityLogger\modules\models\ActivityLogSearch
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="logger-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['index'],
        'layout' => 'inline',
    ]); ?>

    <?= $form->field($model, 'entityName')->dropDownList($model->getEntityNameList(), ['prompt' => '']) ?>

    <?= $form->field($model, 'date')->input('date', [
        'max' => date('Y-m-d'),
    ]) ?>

    <?= Html::a(Yii::t('lav45/logger', 'Reset'), ['index'], [
        'class' => 'btn btn-default',
    ]) ?>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
    $('#{$form->id}').on('change', function() {
        $(this).submit();
    })
JS
);