<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

namespace craft\storehours;

use Craft;
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
     * @var string[] field options table columns
     */
    public $columns;

    /**
     * @var string[] The row labels
     */
    public $slots;

    /**
     * @var string[] The column labels
     */
    public $columnHeadings;

    /**
     * @var string[] The row labels
     */
    public $rowHeadings;



    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
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

        if (!is_array($value) || empty($this->columnHeadings)) {
            return null;
        }

        for ($day = 0; $day <= 6; $day++) {
            foreach ($this->slots as $slot) {
                if (
                    isset($value[$day][$slot['handle']]) &&
                    ($date = DateTimeHelper::toDateTime($value[$day][$slot['handle']])) !== false
                ) {
                    $normalized[$day][$slot['handle']] = $date;
                } else {
                    $normalized[$day][$slot['handle']] = null;
                }
            }
        }

        return $normalized;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValueEX($value, ElementInterface $element = null)
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decode($value);
        } else if ($value === null && $this->isFresh($element) && is_array($this->defaults)) {
            $value = array_values($this->defaults);
        }

        if (!is_array($value) || empty($this->columns)) {
            return null;
        }

        // Normalize the values and make them accessible from both the col IDs and the handles
        foreach ($value as &$row) {
            foreach ($this->columns as $colId => $col) {
                $row[$colId] = $this->_normalizeCellValue($col['type'], $row[$colId] ?? null);
                if ($col['handle']) {
                    $row[$col['handle']] = $row[$colId];
                }
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        $serialized = [];

        for ($day = 0; $day <= 6; $day++) {
            foreach ($this->slots as $slot) {
                $timeValue = $value[$day][$slot['handle']];
                if ($timeValue instanceof \DateTime) {
                    $serialized[$day][$slot['handle']] = $timeValue->format(\DateTime::ATOM);
                } else {
                    $serialized[$day][$slot['handle']] = null;
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
    public function getSettingsHtml()
    {
        $columns = $this->columns;
        $slots = $this->slots;


        if (empty($columns)) {
            $columns = [
                'label' => [
                    'heading' => 'Label',
                    'handle' => 'label',
                    'type' => 'singleline',
                    'autopopulate' => 'handle'
                ],
                'handle' => [
                    'heading' => 'Handle',
                    'handle' => 'handle',
                    'type' => 'singleline'
                ]
            ];
        }


        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField', [
            [
                'label' => Craft::t('app', 'Time Slots'),
                'instructions' => Craft::t('app', 'Add custom time slots.'),
                'id' => 'slots',
                'name' => 'slots',
                'cols' => $columns,
                'rows' => $slots,
                'addRowLabel' => Craft::t('app', 'Add a column'),
                'initJs' => true
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $slots = $this->slots;

        $typeOptions = [
            'singleline' => Craft::t('app', 'Single-line text'),
            'time' => Craft::t('app', 'Time'),
            'color' => Craft::t('app', 'Color'),
        ];

        foreach ($slots as $slot) {
            $slotLabels[] = [
                $slot['label'] => [
                    'heading' => Craft::t('app', $slot['label']),
                    'type' => 'time',
                ],
            ];
        }

        $columnHeadings = array_map(function($a) {return array_pop($a);}, $slotLabels);

        $slotLabels = [
            'heading' => [
                'heading' => Craft::t('app', '' ),
                'type' => 'heading',
            ],
        ];

        array_unshift($columnHeadings, $slotLabels['heading']);

        $startDay = Craft::$app->getUser()->getIdentity()->getPreference('weekStartDay') ?? Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay;

        $days = range($startDay, 6, 1);
        if ($startDay != 0) {
            $days = array_merge($days, range(0, $startDay - 1, -1));
        }

        foreach ($days as $day) {
            $weekDays[] = [
                'heading' => [
                    'heading' => Craft::t('app', Craft::$app->getLocale()->getWeekDayName($day)),
                    'type' => 'heading',
                ],
            ];

            $rowHeadings = array_map(function($a) {return array_pop($a);}, $weekDays);
        }

        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField', [
            [
                'instructions' => Craft::t('app', 'Add Store Hours.'),
                'id' => 'slots',
                'name' => 'slots',
                'cols' => $columnHeadings,
                'rows' => $rowHeadings,
                'initJs' => false,
                'staticRows' => true
            ]
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
