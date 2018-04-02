<?php
/**
 * @var $this yii\web\View
 * @var $model lav45\activityLogger\modules\models\ActivityLogSearch
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use dosamigos\datepicker\DatePicker;

?>

<div class="employee-forwards-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['index'],
        'layout' => 'inline',
    ]); ?>

    <?= $form->field($model, 'entityName')->dropDownList($model->getEntityNameList(), [
        'prompt' => '',
        'onchange' => '',
    ]) ?>

    <?= $form->field($model, 'date')->widget(DatePicker::class, [
        'language' => 'ru',
        'clientOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'dd.mm.yyyy',
            'endDate' => date('d.m.Y'),
            'clearBtn' => true,
        ],
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