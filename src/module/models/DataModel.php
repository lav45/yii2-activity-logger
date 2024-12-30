<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\module\models;

use yii\base\BaseObject;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;

class DataModel extends BaseObject
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var string|\Closure
     */
    private $format;
    /**
     * @var string|array|Formatter
     */
    public $formatter = 'formatter';

    public function init(): void
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
     * @param string|\Closure $value
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
        return $this->formattedValue($values);
    }

    /**
     * @return null|string
     */
    public function getNewValue()
    {
        $values = $this->getValue('new');
        return $this->formattedValue($values);
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function formattedValue($value)
    {
        if ($this->format && is_string($this->format)) {
            return $this->formatter->format($value, $this->format);
        }
        if ($this->format && is_callable($this->format)) {
            $value = call_user_func($this->format, $value);
            return $value ?? $this->formatter->nullDisplay;
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
            $value = json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            return Html::tag('pre', $value);
        }
        return $value;
    }

    /**
     * @return mixed
     */
    protected function getValue(string $tag)
    {
        return ArrayHelper::getValue($this->data, [$tag, 'value']);
    }
}
