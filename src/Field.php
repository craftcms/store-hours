<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use yii\db\Schema;

/**
 * Store Hours represents a Store Hours field.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Field extends \craft\base\Field
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('store-hours', 'Store Hours');
    }

    // Public Methods
    // =========================================================================

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

        $normalized = [];
        $times = ['open', 'close'];

        for ($day = 0; $day <= 6; $day++) {
            foreach ($times as $time) {
                if (
                    isset($value[$day][$time]) &&
                    ($date = DateTimeHelper::toDateTime($value[$day][$time])) !== false
                ) {
                    $normalized[$day][$time] = $date;
                } else {
                    $normalized[$day][$time] = null;
                }
            }
        }

        return $normalized;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        $serialized = [];
        $times = ['open', 'close'];

        for ($day = 0; $day <= 6; $day++) {
            foreach ($times as $time) {
                $timeValue = $value[$day][$time];
                if ($timeValue instanceof \DateTime) {
                    $serialized[$day][$time] = $timeValue->format(\DateTime::ATOM);
                } else {
                    $serialized[$day][$time] = null;
                }
            }
        }

        return $serialized;
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return '';
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
