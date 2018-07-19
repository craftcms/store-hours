<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
    /**
     * Returns today’s hours.
     *
     * @return array
     */
    public function getToday(): array
    {
        $today = (int)(new \DateTime())->format('w');
        return $this[$today];
    }
}
