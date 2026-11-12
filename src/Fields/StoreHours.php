<?php

declare(strict_types=1);

namespace CraftCms\StoreHours\Fields;

use CraftCms\Cms\Element\Contracts\ElementInterface;
use CraftCms\Cms\Field\Field;
use CraftCms\Cms\Field\FieldContext;
use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Support\DateTimeHelper;
use CraftCms\Cms\Support\Facades\I18N;
use CraftCms\Cms\Support\Json;
use CraftCms\Cms\Support\Query;
use CraftCms\Cms\Translation\Locale;
use CraftCms\Cms\Ui\Contracts\Control;
use CraftCms\Cms\Ui\Controls\Choice;
use CraftCms\Cms\Ui\Controls\Table;
use CraftCms\Cms\Ui\Nodes\Field as UiField;
use CraftCms\Cms\Ui\Ui;
use CraftCms\Cms\Ui\UiContext;
use CraftCms\StoreHours\Data\DayData;
use CraftCms\StoreHours\Data\FieldData;
use CraftCms\StoreHours\Gql\Types\Day;
use CraftCms\StoreHours\Gql\Types\Generators\DayType;
use DateTimeInterface;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;
use Override;

use function CraftCms\Cms\t;

/**
 * A Store Hours field: one row per day of the week, with a time for each of the field’s time slots.
 *
 * @phpstan-type Slot array{name: string, handle: string}
 *
 * @since 2.0
 */
class StoreHours extends Field
{
    /**
     * @var array<string, Slot> The time slots that should be shown in the field, indexed by their IDs.
     */
    public array $slots = [];

    /**
     * @var int|null The day the field should start with (0–6, Sunday first), or `null` for the user’s preference.
     *
     * @since 4.1.0
     */
    public ?int $startDay = null;

    #[Override]
    public static function displayName(): string
    {
        return t('Store Hours', category: 'store-hours');
    }

    #[Override]
    public static function icon(): string
    {
        return 'clock';
    }

    #[Override]
    public static function phpType(): string
    {
        return '\\'.FieldData::class;
    }

    #[Override]
    public static function dbType(): string
    {
        return Query::TYPE_JSON;
    }

    public function __construct($config = [])
    {
        if (isset($config['slots']) && is_array($config['slots'])) {
            $config['slots'] = array_map(fn (array $slot) => [
                'name' => (string) ($slot['name'] ?? ''),
                'handle' => (string) ($slot['handle'] ?? ''),
            ], array_filter($config['slots'], is_array(...)));
        } else {
            unset($config['slots']);
        }

        if (array_key_exists('startDay', $config)) {
            $config['startDay'] = is_numeric($config['startDay']) ? (int) $config['startDay'] : null;
        }

        parent::__construct($config);

        if ($this->slots === []) {
            $this->slots = [
                'open' => ['name' => 'Opening Time', 'handle' => 'open'],
                'close' => ['name' => 'Closing Time', 'handle' => 'close'],
            ];
        }
    }

    #[Override]
    public function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'startDay' => ['nullable', 'integer', 'between:0,6'],
        ]);
    }

    #[Override]
    public function settingsUi(UiContext $context = new UiContext): Ui
    {
        $days = array_map(fn (int $day) => [
            'label' => I18N::getLocale()->getWeekDayName($day, Locale::LENGTH_FULL),
            'value' => $day,
        ], $this->dayRange(false));

        return Ui::make([
            UiField::make(t('Time Slots', category: 'store-hours'))
                ->instructions(t('Define the time slots that authors should be able to fill times in for.', category: 'store-hours'))
                ->control(Table::make('slots')
                    ->columns([
                        'name' => ['heading' => t('Name'), 'type' => 'singleline'],
                        'handle' => ['heading' => t('Handle'), 'type' => 'singleline', 'code' => true, 'autopopulate' => 'name'],
                    ])
                    ->keyed()
                    ->allowAdd()
                    ->allowDelete()
                    ->allowReorder()
                    ->minRows(1)
                    ->addRowLabel(t('Add a time slot', category: 'store-hours'))
                    ->value($this->slots)),
            UiField::make(t('Start Day', category: 'store-hours'))
                ->instructions(t('Choose which day the field should start with.', category: 'store-hours'))
                ->control(Choice::make('startDay')
                    ->options($days)
                    ->placeholder(t('Same as user’s preference', category: 'store-hours'))
                    ->value($this->startDay)),
        ]);
    }

    /**
     * Rows are listed in display order, so each one carries its day index in a hidden `day` column.
     */
    #[Override]
    public function uiControl(FieldContext $context): Control
    {
        $value = $context->value instanceof FieldData
            ? $context->value
            : $this->normalizeValue($context->value, $context->element);

        $columns = [
            'heading' => ['heading' => t('Day', category: 'store-hours'), 'type' => 'heading'],
            'day' => ['heading' => '', 'type' => 'hidden'],
        ];

        foreach ($this->slots as $slotId => $slot) {
            $columns[$slotId] = [
                'heading' => t($slot['name'], category: 'site', locale: $context->element?->getLanguage()),
                'type' => 'time',
            ];
        }

        $rows = [];
        foreach ($this->dayRange(true) as $day) {
            $row = [
                'heading' => I18N::getLocale()->getWeekDayName($day, Locale::LENGTH_FULL),
                'day' => (string) $day,
            ];
            foreach (array_keys($this->slots) as $slotId) {
                $row[$slotId] = $value[$day][$slotId]?->format('H:i');
            }
            $rows[] = $row;
        }

        return Table::make($context->path)
            ->columns($columns)
            ->value($rows);
    }

    #[Override]
    public function normalizeValue(mixed $value, ?ElementInterface $element): FieldData
    {
        return $this->createFieldData($value, false);
    }

    #[Override]
    public function normalizeValueFromRequest(mixed $value, ?ElementInterface $element): FieldData
    {
        return $this->createFieldData($value, true);
    }

    /**
     * Stored values are a list of seven days (Sunday first) keyed by slot ID. Submitted rows may come in display
     * order with a `day` key, and GraphQL input may use slot handles.
     */
    private function createFieldData(mixed $value, bool $fromRequest): FieldData
    {
        if ($value instanceof FieldData) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $value = Json::decodeIfJson($value);
        }

        $rows = [];
        foreach (is_array($value) ? $value : [] as $index => $row) {
            if (is_array($row)) {
                $day = isset($row['day']) && is_numeric($row['day']) ? (int) $row['day'] : $index;
                $rows[$day] = $row;
            }
        }

        $data = [];
        for ($day = 0; $day <= 6; $day++) {
            $dayData = [];
            foreach ($this->slots as $slotId => $slot) {
                $cell = $rows[$day][$slotId] ?? ($slot['handle'] !== '' ? $rows[$day][$slot['handle']] ?? null : null);
                $dayData[$slotId] = $this->normalizeTime($cell, $fromRequest);
                if ($slot['handle'] !== '') {
                    $dayData[$slot['handle']] = $dayData[$slotId];
                }
            }
            $data[] = new DayData($day, $dayData);
        }

        return new FieldData($data);
    }

    /**
     * Submitted times are local to the site, while stored ones are in UTC.
     */
    private function normalizeTime(mixed $value, bool $fromRequest): ?DateTimeInterface
    {
        if ($fromRequest && is_string($value) && $value !== '') {
            $value = ['time' => $value];
        }

        return DateTimeHelper::toDateTime($value, assumeSystemTimeZone: is_array($value)) ?: null;
    }

    /**
     * Times are saved as ISO-8601 strings, as in Store Hours 4.
     *
     * @return list<array<string, string|null>>
     */
    #[Override]
    public function serializeValue(mixed $value, ?ElementInterface $element): array
    {
        $serialized = [];

        foreach ($this->createFieldData($value, false) as $day) {
            $row = [];
            foreach (array_keys($this->slots) as $slotId) {
                $row[$slotId] = isset($day[$slotId]) ? (DateTimeHelper::toIso8601($day[$slotId]) ?: null) : null;
            }
            $serialized[] = $row;
        }

        return $serialized;
    }

    #[Override]
    public function isValueEmpty(mixed $value, ElementInterface $element): bool
    {
        return $this->createFieldData($value, false)->getIsAllBlank();
    }

    #[Override]
    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        return '';
    }

    #[Override]
    public function getContentGqlType(): ListOfType
    {
        return Type::listOf(DayType::generateType($this));
    }

    #[Override]
    public function getContentGqlMutationArgumentType(): Type
    {
        $typeName = "{$this->handle}_DayInput";

        /** @var InputObjectType $type */
        $type = GqlEntityRegistry::getOrCreate($typeName, fn () => new InputObjectType([
            'name' => $typeName,
            'fields' => fn () => Day::prepareFieldDefinition($this->slots),
        ]));

        return Type::listOf($type);
    }

    /**
     * Returns the days of the week in the order the field should list them.
     *
     * @return list<int>
     */
    private function dayRange(bool $useFieldSetting): array
    {
        $startDay = $useFieldSetting && $this->startDay !== null
            ? $this->startDay
            : DateTimeHelper::firstWeekDay();

        return [...range($startDay, 6), ...($startDay > 0 ? range(0, $startDay - 1) : [])];
    }
}
