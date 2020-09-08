<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\models;

use yii\base\BaseObject;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;

/**
 * Class DataModel
 * @package lav45\activityLogger\modules\models
 */
class DataModel extends BaseObject
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var string|\Closure|null
     */
    private $format;
    /**
     * @var string|array|Formatter
     */
    public $formatter = 'formatter';

    public function init()
    {
        $this->formatter = Instance::ensure($this->formatter, Formatter::class);
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setData(array $value)
    {
        $this->data = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|\Closure|null $value
     * @return $this
     */
    public function setFormat($value)
    {
        $this->format = $value;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOldValue()
    {
        $values = $this->getValue('old');
        $values = $this->formattedValue($values);
        return $values;
    }

    /**
     * @return null|string
     */
    public function getNewValue()
    {
        $values = $this->getValue('new');
        $values = $this->formattedValue($values);
        return $values;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function formattedValue($value)
    {
        if (is_string($this->format)) {
            return $this->formatter->format($value, $this->format);
        }
        if (is_callable($this->format)) {
            $value = call_user_func($this->format, $value);
            if (null === $value) {
                return $this->formatter->nullDisplay;
            }
            return $value;
        }
        if (null === $value) {
            return $this->formatter->nullDisplay;
        }
        if (is_numeric($value)) {
            return $value;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return Html::a(Html::encode($value), $value, ['target' => '_blank']);
        }
        if (is_string($value)) {
            if (empty($value)) {
                return $this->formatter->nullDisplay;
            }
            return $this->formatter->asNtext($value);
        }
        if (is_bool($value)) {
            return $this->formatter->asBoolean($value);
        }
        if (is_array($value)) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
            $value = Html::tag('pre', $value);
            return $value;
        }
        return $value;
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
