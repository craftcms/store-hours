<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

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
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('store-hours', 'Store Hours');
    }

    // Properties
    // =========================================================================

    /**
     * @var string[] The time slot handles
     */
    public $slots = ['open', 'close'];

    /**
     * @var string[] The time slot labels
     */
    public $slotLabels;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->slotLabels === null) {
            $this->slotLabels = [
                Craft::t('store-hours', 'Opening Time'),
                Craft::t('store-hours', 'Closing Time')
            ];
        }

        parent::init();
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

        $normalized = [];

        for ($day = 0; $day <= 6; $day++) {
            foreach ($this->slots as $slot) {
                if (
                    isset($value[$day][$slot]) &&
                    ($date = DateTimeHelper::toDateTime($value[$day][$slot])) !== false
                ) {
                    $normalized[$day][$slot] = $date;
                } else {
                    $normalized[$day][$slot] = null;
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

        for ($day = 0; $day <= 6; $day++) {
            foreach ($this->slots as $slot) {
                $timeValue = $value[$day][$slot];
                if ($timeValue instanceof \DateTime) {
                    $serialized[$day][$slot] = $timeValue->format(\DateTime::ATOM);
                } else {
                    $serialized[$day][$slot] = null;
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
        for ($day = 0; $day <= 6; $day++) {
            foreach ($this->slots as $slot) {
                if (isset($value[$day][$slot])) {
                    return false;
                }
            }
        }

        return true;
    }
}
