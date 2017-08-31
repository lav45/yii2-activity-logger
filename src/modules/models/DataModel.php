<?php

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;

/**
 * Class DataModel
 * @package lav45\activityLogger\modules\models
 */
class DataModel
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var string|array|Formatter
     */
    public $formatter = 'formatter';

    /**
     * DataModel constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return Formatter
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFormatter()
    {
        if (!$this->formatter instanceof Formatter) {
            $this->formatter = Instance::ensure($this->formatter, Formatter::class);
        }
        return $this->formatter;
    }

    /**
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getOldValue()
    {
        $values = $this->getValue('old');
        $values = $this->formattedValue($values);
        return $values;
    }

    /**
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getNewValue()
    {
        $values = $this->getValue('new');
        $values = $this->formattedValue($values);
        return $values;
    }

    /**
     * @param mixed $values
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function formattedValue($values)
    {
        if (is_string($values)) {
            return Html::encode(Yii::t('app', $values));
        }
        if (is_null($values)) {
            return $this->getFormatter()->nullDisplay;
        }
        if (is_bool($values)) {
            return $this->getFormatter()->asBoolean($values);
        }
        return $values;
    }

    /**
     * @param string $tag
     * @return mixed
     */
    protected function getValue($tag)
    {
        return ArrayHelper::getValue($this->data, [$tag, 'value']);
    }
}