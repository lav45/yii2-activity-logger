<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\models;

use Yii;

class ActivityLogViewModel extends ActivityLog
{
    /**
     * @var DataModel|string|array
     */
    public $dataModel = DataModel::class;
    /**
     * [ entity_name => Entity::class ]
     */
    public array $entityMap = [];

    private array $entityModel = [];

    /**
     * @param array $row
     * @return ActivityLog|object|static
     */
    public static function instantiate($row)
    {
        return Yii::createObject(static::class);
    }

    /**
     * @return \yii\base\Model|null
     */
    protected function getEntityModel()
    {
        if (isset($this->entityModel[$this->entity_name]) === false) {
            $this->entityModel[$this->entity_name] = $this->getEntityObject($this->entity_name);
        }
        return $this->entityModel[$this->entity_name] ?: null;
    }

    /**
     * @return false|\yii\base\Model
     */
    private function getEntityObject(string $id)
    {
        if (isset($this->entityMap[$id]) === false) {
            return false;
        }
        /** @var \yii\base\Model $class */
        $class = $this->entityMap[$id];
        return $class::instance();
    }

    /**
     * @return \Generator|DataModel[]
     */
    public function getData(): iterable
    {
        foreach (parent::getData() as $attribute => $values) {
            if (is_string($values)) {
                $label = is_string($attribute) ? $this->getEntityAttributeLabel($attribute) : $attribute;
                yield $label => $values;
            } else {
                $dataModel = $this->getDataModel()
                    ->setFormat($this->getAttributeFormat($attribute))
                    ->setData($values);

                yield $this->getEntityAttributeLabel($attribute) => $dataModel;
            }
        }
    }

    protected function getDataModel(): DataModel
    {
        if (!is_object($this->dataModel)) {
            $this->dataModel = Yii::createObject($this->dataModel);
        }
        return $this->dataModel;
    }

    protected function getEntityAttributeLabel(string $attribute): string
    {
        if ($entityModel = $this->getEntityModel()) {
            return $entityModel->getAttributeLabel($attribute);
        }
        return $this->generateAttributeLabel($attribute);
    }

    protected function getEntityAttributeFormats(): array
    {
        $entityModel = $this->getEntityModel();
        if (null !== $entityModel && method_exists($entityModel, 'attributeFormats')) {
            return $entityModel->attributeFormats();
        }
        return [];
    }

    protected function getAttributeFormat(string $attribute): ?string
    {
        $formats = $this->getEntityAttributeFormats();
        return $formats[$attribute] ?? null;
    }
}
