<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours\data;

use Craft;

/**
 * Class FieldData
 */
class DayData extends \ArrayObject
{
    /**
     * @var int The day index (0-6)
     */
    public $dayIndex;

    /**
     * @param int $dayIndex
     * @param array $input
     */
    public function __construct(int $dayIndex, array $input)
    {
        $this->dayIndex = $dayIndex;
        parent::__construct($input);
    }

    /**
     * Returns the day name
     *
     * @param string|null $length The format length that should be returned. Values: `\craft\i18n\Locale::LENGTH_ABBREVIATED`, `::SHORT`, `::MEDIUM`, `::FULL`
     * @return string
     */
    public function getName(string $length = null): string
    {
        return Craft::$app->getLocale()->getWeekDayName($this->dayIndex, $length);
    }
}
