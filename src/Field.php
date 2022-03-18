<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours;

use Craft;
use craft\base\ElementInterface;
use craft\elements\User;
use craft\helpers\Cp;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\i18n\Locale;
use craft\storehours\data\DayData;
use craft\storehours\data\FieldData;
use craft\web\assets\timepicker\TimepickerAsset;
use yii\db\Schema;

/**
 * Store Hours represents a Store Hours field.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
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
     * @var array|null The time slots that should be shown in the field
     */
    public $slots;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Create default time slots
        if (empty($this->slots)) {
            $this->slots = [
                'open' => [
                    'name' => 'Opening Time',
                    'handle' => 'open',
                    'type' => 'time',
                ],
                'close' => [
                    'name' => 'Closing Time',
                    'handle' => 'close',
                    'type' => 'time',
                ],
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        $columns = [
            'name' => [
                'heading' => Craft::t('app', 'Name'),
                'type' => 'singleline',
                'autopopulate' => 'handle',
            ],
            'handle' => [
                'heading' => Craft::t('app', 'Handle'),
                'code' => true,
                'type' => 'singleline',
            ],
        ];

        $view = Craft::$app->getView();

        $jsId = Json::encode($view->namespaceInputId('slots'));
        $jsName = Json::encode($view->namespaceInputName('slots'));
        $jsCols = Json::encode($columns);

        $js = <<<JS
new Craft.EditableTable({$jsId}, {$jsName}, {$jsCols}, {
    minRows: 1,
    rowIdPrefix: 'slot'
});
JS;

        $view->registerJs($js);

        return Cp::editableTableFieldHtml([
            'label' => Craft::t('store-hours', 'Time Slots'),
            'instructions' => Craft::t('store-hours', 'Define the time slots that authors should be able to fill times in for.'),
            'id' => 'slots',
            'name' => 'slots',
            'cols' => $columns,
            'rows' => $this->slots,
            'addRowLabel' => Craft::t('store-hours', 'Add a time slot'),
            'initJs' => false,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        Craft::$app->getView()->registerAssetBundle(TimepickerAsset::class);

        return '<input type="hidden" name="' . $this->handle . '" value="">' .
            $this->_getInputHtml($value, false);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
            ksort($value);
        } elseif ($value === null && $this->isFresh($element) && is_array($this->slots)) {
            $value = [];
        }

        $data = [];

        for ($day = 0; $day <= 6; $day++) {
            // Normalize the values and make them accessible from both the slot IDs and the handles
            $dayData = [];
            foreach ($this->slots as $slotId => $slot) {
                $dayData[$slotId] = DateTimeHelper::toDateTime($value[$day][$slotId] ?? null) ?: null;
                if ($slot['handle']) {
                    $dayData[$slot['handle']] = $dayData[$slotId];
                }
            }
            $data[] = new DayData($day, $dayData);
        }

        return new FieldData($data);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        /** @var FieldData $value */
        $serialized = [];

        foreach ($value as $row) {
            $serializedRow = [];
            foreach (array_keys($this->slots) as $colId) {
                $serializedRow[$colId] = parent::serializeValue($row[$colId] ?? null);
            }
            $serialized[] = $serializedRow;
        }

        return $serialized;
    }

    /**
     * @inheritdoc
     */
    public function isValueEmpty(mixed $value, ElementInterface $element): bool
    {
        /** @var FieldData $value */
        return $value->getIsAllBlank();
    }

    /**
     * @inheritdoc
     */
    public function getStaticHtml(mixed $value, ElementInterface $element): string
    {
        return $this->_getInputHtml($value, true);
    }

    /**
     * Returns the field's input HTML.
     *
     * @param FieldData $value
     * @param bool $static
     * @return string
     */
    private function _getInputHtml(FieldData $value, bool $static): string
    {
        if (empty($this->slots)) {
            return '';
        }

        $columns = [
            'day' => [
                'heading' => '',
                'type' => 'heading',
            ],
        ];

        foreach ($this->slots as $slotId => $slot) {
            $columns[$slotId] = [
                'heading' => Craft::t('site', $slot['name']),
                'type' => 'time',
            ];
        }

        // Get the day key order per the user's Week Start Day pref
        /** @var User $user */
        $user = Craft::$app->getUser()->getIdentity();
        $startDay = (int)($user->getPreference('weekStartDay') ?? Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay);
        $days = range($startDay, 6, 1);
        if ($startDay !== 0) {
            $days = array_merge($days, range(0, $startDay - 1, -1));
        }

        // Build out the editable table rows, explicitly setting each cell value to an array with a 'value' key
        $locale = Craft::$app->getLocale();
        $rows = [];
        foreach ($days as $day) {
            $row = [
                'day' => $locale->getWeekDayName($day, Locale::LENGTH_FULL),
            ];

            $data = $value[(string)$day] ?? [];
            foreach ($this->slots as $slotId => $col) {
                $row[$slotId] = [
                    'value' => $data[$slotId] ?? null,
                ];
            }

            $rows[(string)$day] = $row;
        }

        $view = Craft::$app->getView();
        return $view->renderTemplate('_includes/forms/editableTable', [
            'id' => $view->formatInputId($this->handle),
            'name' => $this->handle,
            'cols' => $columns,
            'rows' => $rows,
            'static' => $static,
            'staticRows' => true,
        ]);
    }
}
