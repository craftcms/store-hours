<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours\data;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
    /**
     * Returns today’s hours.
     *
     * @return DayData
     */
    public function getToday(): DayData
    {
        $today = (int)(new \DateTime())->format('w');
        return $this[$today];
    }
}
