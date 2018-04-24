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

        $counter = 0;
        $first = null;
        $second = null;

        foreach ($test as $key => $slot) {
            $counter = 0;

            if (($key % 2) == 0) {
                $first = $slot;
            }
            if (($key % 2) != 0) {
                $second = $slot;
            }
            
            if ($first != null and $second != null) {
                if ($first < $currentDateTime and $currentDateTime < $second) {
                    $return = 'OPEN';
                    $first = null;
                    $second = null;
                    break;
                }
                $first = null;
                $second = null;
            }
            // checks if the current time is within the first open-close range
            $return = 'CLOSED';

        }
            return $return;
    }

}