<?php

/**
 * @copyright   2022 Powertic. All rights reserved
 * @author      Luiz Eduardo Oliveira Fonseca <luizeof@gmail.com>
 *
 * @link        https://powertic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\PowerticSmsBundle\Core;

use Exception;
use Mautic\PluginBundle\Helper\IntegrationHelper;

class Configuration
{
	/**
	 * @var IntegrationHelper
	 */
	private $integrationHelper;

	/**
	 * @var string
	 */
	private $sendingPhoneNumber;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $apikey;

	/**
	 * @var bool
	 */
	private $disableTrackableUrls = true;

	/**
	 * Configuration constructor.
	 */
	public function __construct(IntegrationHelper $integrationHelper)
	{
		$this->integrationHelper = $integrationHelper;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 */
	public function getUrl()
	{
		$this->setConfiguration();

		return $this->url;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 */
	public function getApiKey()
	{
		$this->setConfiguration();

		return $this->apikey;
	}

	/**
	 * @throws Exception
	 */
	private function setConfiguration()
	{
		if ($this->url) {
			return;
		}

		$integration = $this->integrationHelper->getIntegrationObject('PowerticSms');

		if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
			throw new Exception();
		}

		$keys = $integration->getDecryptedApiKeys();
		if (empty($keys['url']) || empty($keys['apikey'])) {
			throw new Exception("PowerticSms configuration not set.");
		}

		$this->url = $keys['url'];
		$this->apikey = $keys['apikey'];
	}
}
