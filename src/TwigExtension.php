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
            new \Twig_SimpleFilter('isOpen', array($this, 'isOpen')),
        ];
    }

    /**
     * @inheritdoc
     */
    public function isOpen($handleOne)
    {
        $field = new Field();
        $test = $field->getDailyTimeSlots($handleOne);
        $currentDateTime = DateTimeHelper::toDateTime(DateTimeHelper::currentTimeStamp());
        $openTime = $test['open'];
        $closeTime = $test['close'];
        if($openTime < $currentDateTime and $currentDateTime < $closeTime) {
            return 'Open';
        }
        return 'Closed';
    }


}