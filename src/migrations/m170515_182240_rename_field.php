<?php

namespace craft\storehours\migrations;

use Craft;
use craft\db\Migration;
use craft\storehours\Field;

/**
 * m170515_182240_rename_field migration.
 */
class m170515_182240_rename_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', ['type' => Field::class], ['type' => 'craft\storehours\fields\StoreHoursField']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170515_182240_rename_field cannot be reverted.\n";
        return false;
    }
}
