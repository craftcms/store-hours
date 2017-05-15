<?php

namespace craft\storehours;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use DateTime;
use yii\db\Schema;

/**
 * Store Hours field type
 *
 * @property string $contentColumnType
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  2.0
 */
class Field extends \craft\base\Field
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
        if (!$value) {
            return null;
        }

        foreach ($value as $key => $day) {
            // Validate the open time
            if (!empty($day['open']['time']) && DateTimeHelper::toDateTime($day['open']) === false) {
                $value[$key]['open']['time'] = null;
            }

            // Validate the closing time
            if (!empty($day['close']['time']) && DateTimeHelper::toDateTime($day['close']) === false) {
                $value[$key]['close']['time'] = null;
            }
        }

        return parent::serializeValue($value, $element);
    }
}
