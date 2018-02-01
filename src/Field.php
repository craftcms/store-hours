<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace craft\storehours;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\fields\data\ColorData;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\validators\ColorValidator;
use craft\web\assets\timepicker\TimepickerAsset;
use yii\db\Schema;

/**
 * Store Hours represents a Store Hours field.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class Field extends craft\base\Field
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Store Hours');
    }

    // Properties
    // =========================================================================

    /**
     * @var array|null The columns that should be shown in the table
     */
    public $columns;

    /**
     * @var array|null The days of the week that should be shown as row headings in the table
     */
    public $rowHeadings;

    /**
     * @var string The type of database column the field should have in the content table
     */
    public $columnType = Schema::TYPE_TEXT;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Create default time slots
        if (empty($this->columns)) {
            $this->columns = [
                'Opening Time' => [
                    'heading' => Craft::t('app', 'Opening Time'),
                    'handle' => Craft::t('app', 'openingTime'),
                    'type' => 'time'
                ],
                'Closing Time' => [
                    'heading' => Craft::t('app', 'Closing Time'),
                    'handle' => Craft::t('app', 'closeingTime'),
                    'type' => 'time'
                ]
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return $this->columnType;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $typeOptions = [
            'time' => Craft::t('app', 'Time'),
        ];

        $columnSettings = [
            'heading' => [
                'heading' => Craft::t('app', 'Time Slot Heading'),
                'type' => 'singleline',
                'autopopulate' => 'handle'
            ],
            'handle' => [
                'heading' => Craft::t('app', 'Handle'),
                'code' => true,
                'type' => 'singleline'
            ],
            'width' => [
                'heading' => Craft::t('app', 'Width'),
                'code' => true,
                'type' => 'singleline',
                'width' => 50
            ],
            'type' => [
                'heading' => Craft::t('app', 'Type'),
                'class' => 'thin',
                'type' => 'select',
                'options' => $typeOptions,
            ],
        ];

        $view = Craft::$app->getView();

        $columnsField = $view->renderTemplateMacro('_includes/forms', 'editableTableField', [
            [
                'label' => Craft::t('app', 'Time Slots'),
                'instructions' => Craft::t('app', 'Define the time slots your  store hours table should have.'),
                'id' => 'columns',
                'name' => 'columns',
                'cols' => $columnSettings,
                'rows' => $this->columns,
                'addRowLabel' => Craft::t('app', 'Add a column'),
                'initJs' => true
            ]
        ]);

        return $view->renderTemplate('store-hours/settings', [
            'field' => $this,
            'columnsField' => $columnsField,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        Craft::$app->getView()->registerAssetBundle(TimepickerAsset::class);

        $input = '<input type="hidden" name="'.$this->handle.'" value="">';

        $tableHtml = $this->_getInputHtml($value, $element, false);

        if ($tableHtml) {
            $input .= $tableHtml;
        }

        return $input;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateTableData'];
    }

    /**
     * Validates the table data.
     *
     * @param ElementInterface $element
     */
    public function validateTableData(ElementInterface $element)
    {
        /** @var Element $element */
        $value = $element->getFieldValue($this->handle);

        if (!empty($value) && !empty($this->columns)) {
            foreach ($value as $row) {
                foreach ($this->columns as $colId => $col) {
                    if (!$this->_validateCellValue($col['type'], $row[$colId], $error)) {
                        $element->addError($this->handle, $error);
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        } else if ($value === null && $this->isFresh($element) && is_array($this->columns)) {
            $value = [];
        }

        if (!is_array($value) || empty($this->columns)) {
            return null;
        }

        for ($day = 0; $day <= 6; $day++) {
            // Normalize the values and make them accessible from both the col IDs and the handles
            foreach ($this->columns as $colId => $col) {
                $value[$day][$colId] = $this->_normalizeCellValue($col['type'], $value[$day][$colId] ?? null);
                if ($col['handle']) {
                    $value[$day][$col['handle']] = $value[$day][$colId];
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
        if (!is_array($value) || empty($this->columns)) {
            return null;
        }

        $serialized = [];

        foreach ($value as $row) {
            $serializedRow = [];
            foreach (array_keys($this->columns) as $colId) {
                $serializedRow[$colId] = parent::serializeValue($row[$colId] ?? null);
            }
            $serialized[] = $serializedRow;
        }

        return $serialized;
    }

    /**
     * @inheritdoc
     */
    public function getStaticHtml($value, ElementInterface $element): string
    {
        return $this->_getInputHtml($value, $element, true);
    }

    // Private Methods
    // =========================================================================

    /**
     * Normalizes a cell’s value.
     *
     * @param string $type  The cell type
     * @param mixed  $value The cell value
     *
     * @return mixed
     * @see normalizeValue()
     */
    private function _normalizeCellValue(string $type, $value)
    {
        if ($type == 'time') {
            return DateTimeHelper::toDateTime($value) ?: null;
        }

        return $value;
    }

    /**
     * Validates a cell’s value.
     *
     * @param string      $type   The cell type
     * @param mixed       $value  The cell value
     * @param string|null &$error The error text to set on the element
     *
     * @return bool Whether the value is valid
     * @see normalizeValue()
     */
    private function _validateCellValue(string $type, $value, string &$error = null): bool
    {
        if ($type === 'color' && $value !== null) {
            /** @var ColorData $value */
            $validator = new ColorValidator();
            $validator->message = str_replace('{attribute}', '{value}', $validator->message);
            $hex = $value->getHex();

            return $validator->validate($hex, $error);
        }

        return true;
    }

    /**
     * Returns the field's input HTML.
     *
     * @param mixed                 $value
     * @param ElementInterface|null $element
     * @param bool                  $static
     *
     * @return string|null
     */
    private function _getInputHtml($value, ElementInterface $element = null, bool $static): ?string
    {
        if ($this->rowHeadings === null) {
            $this->rowHeadings = $this->_getWeekDayHeadings();
        }

        /** @var Element $element */
        if (empty($this->columns) || empty($this->rowHeadings)) {
            return null;
        }

        $columns = array_merge([
            'heading' => [
                'heading' => Craft::t('app', ''),
                'type' => 'heading',
            ],
        ], $this->columns);

        // Explicitly set each cell value to an array with a 'value' key
        $checkForErrors = $element && $element->hasErrors($this->handle);
        if (is_array($value)) {
            foreach ($value as $day => &$row) {
                // Add the day heading
                $row['heading'] = $this->rowHeadings[$day]['heading'];
                foreach ($this->columns as $colId => $col) {
                    if ($row[$colId] !== null) {
                        $hasErrors = $checkForErrors && !$this->_validateCellValue($col['type'], $row[$colId]);
                        $row[$colId] = [
                            'value' => $row[$colId],
                            'hasErrors' => $hasErrors,
                        ];
                    }
                }
            }
        }
        unset($row);

        $view = Craft::$app->getView();
        $id = $view->formatInputId($this->handle);

        return $view->renderTemplate('_includes/forms/editableTable', [
            'id' => $id,
            'name' => $this->handle,
            'cols' => $columns,
            'rows' => $value,
            'static' => $static,
            'staticRows' => true
        ]);
    }

    /**
     * Returns an array of weekday headings starting on the user's defaultWeekStartDay.
     *
     * @return array|null
     */
    private function _getWeekDayHeadings(): array
    {
        $userIdentity = Craft::$app->getUser()->getIdentity();
        $generalConfig = Craft::$app->getConfig()->getGeneral();

        $startDay = $userIdentity->getPreference('weekStartDay') ?? $generalConfig->defaultWeekStartDay;

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
        }
        unset($days);

        if ($weekDays) {
            $weekDayHeadings = array_map(function($a) {
                return array_pop($a);
            }, $weekDays);
        }

        return $weekDayHeadings;
    }
}
