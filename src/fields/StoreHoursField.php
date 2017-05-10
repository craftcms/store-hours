<?php
namespace craft\storehours\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Json;
use DateTime;
use Craft;
use yii\db\Schema;

/**
 * Store Hours field type
 *
 * @property string $contentColumnType
 */
class StoreHoursField extends Field
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('storehours', 'Store Hours');
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }


    /**
     * Returns the field's input HTML
     *
     * @param mixed                 $value
     * @param ElementInterface|null $element
     * @return string
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $value = Json::decode($value);

        return Craft::$app->getView()->renderTemplate('storeHours/input', array(
            'id'    => Craft::$app->view->formatInputId($this->handle),
            'name'  => $this->handle,
            'value' => $value,
        ));
    }

    /**
     * Returns the input value as it should be saved to the database.
     *
     * @param mixed $value
     * @return mixed
     */
    public function prepValueFromPost($value)
    {
        $this->_convertTimes($value, Craft::$app->getTimeZone());


        return $value;
    }

    /**
     * Prepares the field's value for use.
     *
     * @param mixed $value
     * @return mixed
     */
    public function prepValue($value)
    {
        $this->_convertTimes($value);



        return $value;
    }

    /**
     * Loops through the data and converts the times to DateTime objects.
     *
     * @access private
     *
     * @param array &$value
     * @param null  $timezone
     *
     * @return null
     */
    private function _convertTimes(&$value, $timezone = null)
    {
        if (is_array($value))
        {
            foreach ($value as &$day)
            {
                if ((is_string($day['open']) && $day['open']) || (is_array($day['open']) && $day['open']['time']))
                {
                    $day['open'] = DateTime::createFromFormat($day['open'], $timezone);
                }
                else if (!($day['open'] instanceof DateTime))
                {
                    $day['open'] = '';
                }

                if ((is_string($day['close']) && $day['close']) || (is_array($day['close']) && $day['close']['time']))
                {
                    $day['close'] = DateTime::createFromFormat($day['close'], $timezone);
                }
                else if (!($day['close'] instanceof DateTime))
                {
                    $day['close'] = '';
                }
            }
        }
    }
}
