<?php

namespace craft\storehours;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use yii\db\Schema;

/**
 * Store Hours field type
 *
 * @property string $contentColumnType
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  2.0
 */
class Field extends \craft\base\Field
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('store-hours', 'Store Hours');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value)) {
            $value = Json::decode($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('store-hours/input', [
            'id' => Craft::$app->view->formatInputId($this->handle),
            'name' => $this->handle,
            'value' => $value,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateTimes'];
    }

    /**
     * Validates the submitted store hours data to make sure it’s all in the right format.
     *
     * @param ElementInterface $element
     */
    public function validateTimes(ElementInterface $element)
    {
        /** @var Element $element */
        $value = (array)$element->getFieldValue($this->handle);
        $normalizedValue = [];
        $times = ['open', 'close'];

        for ($day = 0; $day <= 6; $day++) {
            foreach ($times as $time) {
                if (isset($value[$day][$time]['time']) && DateTimeHelper::toDateTime($value[$day][$time]) !== false) {
                    $normalizedValue[$day][$time] = $value[$day][$time];
                } else {
                    $normalizedValue[$day][$time] = null;
                }
            }
        }

        $element->setFieldValue($this->handle, $normalizedValue);
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value): bool
    {
        $times = ['open', 'close'];
        for ($day = 0; $day <= 6; $day++) {
            foreach ($times as $time) {
                if (isset($value[$day][$time])) {
                    return false;
                }
            }
        }

        return true;
    }
}
