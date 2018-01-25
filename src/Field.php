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
use craft\web\assets\tablesettings\TableSettingsAsset;
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
     * @var array The default row values that new elements should have
     */
    public $defaults;

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

        // Convert default date cell values to ISO8601 strings
        if (!empty($this->columns) && $this->defaults !== null) {
            foreach ($this->columns as $colId => $col) {
                if (in_array($col['type'], ['date', 'time'], true)) {
                    foreach ($this->defaults as &$row) {
                        $row[$colId] = DateTimeHelper::toIso8601($row[$colId]) ?: null;
                    }
                }
            }
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
        if (empty($this->columns)) {
            $this->columns = [
                'col1' => [
                    'heading' => '',
                    'handle' => '',
                    'type' => 'singleline'
                ]
            ];
        }

        if ($this->defaults === null) {
            $this->defaults = ['row1' => []];
        }

        $typeOptions = [
            'checkbox' => Craft::t('app', 'Checkbox'),
            'color' => Craft::t('app', 'Color'),
            'date' => Craft::t('app', 'Date'),
            'lightswitch' => Craft::t('app', 'Lightswitch'),
            'multiline' => Craft::t('app', 'Multi-line text'),
            'number' => Craft::t('app', 'Number'),
            'singleline' => Craft::t('app', 'Single-line text'),
            'time' => Craft::t('app', 'Time'),
        ];

        // Make sure they are sorted alphabetically (post-translation)
        asort($typeOptions);

        $columnSettings = [
            'heading' => [
                'heading' => Craft::t('app', 'Column Heading'),
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

        $view->registerAssetBundle(TimepickerAsset::class);
        $view->registerAssetBundle(TableSettingsAsset::class);
        $view->registerJs('new Craft.TableFieldSettings('.
            Json::encode($view->namespaceInputName('columns'), JSON_UNESCAPED_UNICODE).', '.
            Json::encode($view->namespaceInputName('defaults'), JSON_UNESCAPED_UNICODE).', '.
            Json::encode($this->columns, JSON_UNESCAPED_UNICODE).', '.
            Json::encode($this->defaults, JSON_UNESCAPED_UNICODE).', '.
            Json::encode($columnSettings, JSON_UNESCAPED_UNICODE).
            ');');

        $columnsField = $view->renderTemplateMacro('_includes/forms', 'editableTableField', [
            [
                'label' => Craft::t('app', 'Table Columns'),
                'instructions' => Craft::t('app', 'Define the columns your table should have.'),
                'id' => 'columns',
                'name' => 'columns',
                'cols' => $columnSettings,
                'rows' => $this->columns,
                'addRowLabel' => Craft::t('app', 'Add a column'),
                'initJs' => false
            ]
        ]);


        return $view->renderTemplate('store-hours/input', [
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

        $tableHtml = $this->_getInputHtml($value, $element, false, true);

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
        return $this->_getInputHtml($value, $element, false,true);
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
        switch ($type) {
            case 'color':
                if ($value instanceof ColorData) {
                    return $value;
                }

                if (!$value || $value === '#') {
                    return null;
                }

                $value = strtolower($value);

                if ($value[0] !== '#') {
                    $value = '#'.$value;
                }

                if (strlen($value) === 4) {
                    $value = '#'.$value[1].$value[1].$value[2].$value[2].$value[3].$value[3];
                }

                return new ColorData($value);

            case 'date':
            case 'time':
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
    private function _getInputHtml($value, ElementInterface $element = null, bool $static, bool $staticRows)
    {

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

        /** @var Element $element */
        if (empty($this->columns)) {
            return null;
        }

        // Translate the column headings
        foreach ($this->columns as &$column) {
            if (!empty($column['heading'])) {
                $column['heading'] = Craft::t('site', $column['heading']);
            }
        }
        unset($column);

        // Explicitly set each cell value to an array with a 'value' key
        $checkForErrors = $element && $element->hasErrors($this->handle);
        if (is_array($value)) {
            foreach ($value as &$row) {
                foreach ($this->columns as $colId => $col) {
                    if (isset($row[$colId])) {
                        $hasErrors = $checkForErrors && !$this->_validateCellValue($col['type'], $row[$colId]);
                        $row[$colId] = [
                            'value' => $row[$colId],
                            'heading' => Craft::t('app', Craft::$app->getLocale()->getWeekDayName($day)),
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
            'cols' => $this->columns,
            'rows' => $value,
            'staticRows' => true,
            'static' => $static,
        ]);
    }
}
