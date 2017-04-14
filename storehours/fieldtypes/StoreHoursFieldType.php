<?php
namespace Craft;

/**
 * Store Hours field type
 */
class StoreHoursFieldType extends BaseFieldType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Store Hours';
    }

    /**
	 * Returns the content attribute config.
	 *
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		return AttributeType::Mixed;
	}

	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		return craft()->templates->render('storehours/input', array(
			'id'    => craft()->templates->formatInputId($name),
			'name'  => $name,
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
		$this->_convertTimes($value, craft()->getTimeZone());

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
					$day['open'] = DateTime::createFromString($day['open'], $timezone);
				}
				else if (!($day['open'] instanceof DateTime))
				{
					$day['open'] = '';
				}

				if ((is_string($day['close']) && $day['close']) || (is_array($day['close']) && $day['close']['time']))
				{
					$day['close'] = DateTime::createFromString($day['close'], $timezone);
				}
				else if (!($day['close'] instanceof DateTime))
				{
					$day['close'] = '';
				}
			}
		}
	}
}
