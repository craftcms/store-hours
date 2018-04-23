<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   MIT
 */

namespace craft\storehours;

use Craft;
use craft\helpers\DateTimeHelper;


/**
 * Hexdec Twig Extension
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'isOpen';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('isOpen', [$this, 'isOpen']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function isOpen($value)
    {
        $field = new Field();
        $test = $field->getDailyTimeSlots($value);
        $currentDateTime = DateTimeHelper::toDateTime(DateTimeHelper::currentTimeStamp());
        $openTimeOne = $test['open'];
        $closeTimeOne = $test['lunchTimeOpen'];
        $openTimeTwo = $test['lunchtimeclose'];
        $closeTimeTwo = $test['close'];
        $handle = [$openTimeOne, $closeTimeOne, $openTimeTwo, $closeTimeTwo];

        $return = 'hello';
        foreach ($handle as $key => $slot) {
            $counter = 0;
            $first = null;
            $second = null;
            if (($key % 2) == 0) {
                $first = $slot;
            }
            if (($key % 2) != 0) {
                $second = $slot;
            }

            if ($first == null or $second == null) {
                break;
            }
            
            // checks if the current time is within the first open-close range
            if ($first < $currentDateTime and $currentDateTime < $second) {
                return 'OPEN';
                break;
            }
            return 'CLOSED';
        }
        return $return;
    }

}