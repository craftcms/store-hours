<?php
namespace Craft;

/**
 * Store Hours plugin
 */
class StoreHoursPlugin extends BasePlugin
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Store Hours';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '1.2.2';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Pixel & Tonic';
	}

	/**
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://pixelandtonic.com';
	}

	/**
	 * @return string
	 */
	public function getPluginUrl()
	{
		return 'https://github.com/craftcms/store-hours/tree/v1';
	}

	/**
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return $this->getPluginUrl().'/blob/v1/README.md';
	}

	/**
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/craftcms/store-hours/v1/releases.json';
	}
}
