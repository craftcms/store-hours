<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours\migrations;

use craft\db\Migration;
use craft\storehours\Field;

/**
 * m170515_192840_rename_field migration.
 */
class m170515_192840_rename_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $oldClasses = [
            'StoreHours', // 1.x
            'craft\storehours\fields\StoreHoursField', // 2.0.0
        ];

        $this->update('{{%fields}}', ['type' => Field::class], ['in', 'type', $oldClasses]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170515_192840_rename_field cannot be reverted.\n";
        return false;
    }
}
