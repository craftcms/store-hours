<?php

namespace craft\storehours\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180206_184623_time_slots migration.
 */
class m180206_184623_time_slots extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

    }


    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_184623_time_slots cannot be reverted.\n";
        return false;
    }
}
