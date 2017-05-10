<?php

namespace craft\storehours\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use DateTime;
use Craft;
use yii\db\Schema;

/**
 * Store Hours field type
 *
 * @property string $contentColumnType
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  2.0
 */
class StoreHoursField extends Field
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('storehours', 'Store Hours');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }


    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $value = Json::decode($value);


        return Craft::$app->getView()->renderTemplate('storeHours/input', [
            'id' => Craft::$app->view->formatInputId($this->handle),
            'name' => $this->handle,
            'value' => $value,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        foreach ($value as $key => $day) {

            // Make sure something was selected.
            if (!empty($day['open']) && !empty($day['open']['time'])) {

                // Check to see if they put a valid time in.
                $testTime = DateTimeHelper::toDateTime(['time' => $day['open']['time']]);

                // Not a valid time so nuke it.
                if (!$testTime instanceof DateTime) {
                    $value[$key]['open']['time'] = null;
                }
            }

            // Do the same for closing time.
            if (!empty($day['close']) && !empty($day['close']['time'])) {

                $testTime = DateTimeHelper::toDateTime(['time' => $day['close']['time']]);

                if (!$testTime instanceof DateTime) {
                    $value[$key]['close']['time'] = null;
                }
            }
        }

        return parent::serializeValue($value, $element);
    }
}
