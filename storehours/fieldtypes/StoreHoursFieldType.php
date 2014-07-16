<?php
namespace Craft;

/**
 * Store Hours field type
 */
class StoreHoursFieldType extends BaseFieldType
{
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
		$this->_convertTimes($value);

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
		$this->_convertTimes($value);

		return $value;
	}

	/**
	 * Loops through the data and converts the times to DateTime objects.
	 *
	 * @access private
	 * @param array &$value
	 */
	private function _convertTimes(&$value)
	{
		if (is_array($value))
		{
			foreach ($value as &$day)
			{
				if ((is_string($day['open']) && $day['open']) || (is_array($day['open']) && $day['open']['time']))
				{
					$day['open'] = DateTime::createFromString($day['open']);
				}

				if ((is_string($day['close']) && $day['close']) || (is_array($day['close']) && $day['close']['time']))
				{
					$day['close'] = DateTime::createFromString($day['close']);
				}
			}
		}
	}
}
