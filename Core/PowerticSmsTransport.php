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
use Throwable;
use Psr\Log\LoggerInterface;
use Mautic\LeadBundle\Entity\Lead;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Mautic\SmsBundle\Sms\TransportInterface;

class PowerticSmsTransport implements TransportInterface
{
	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var string
	 */
	private $sendingPhoneNumber;

	/**
	 * TwilioTransport constructor.
	 */
	public function __construct(Configuration $configuration, LoggerInterface $logger)
	{
		$this->logger = $logger;
		$this->configuration = $configuration;
	}

	/**
	 * @param string $content
	 *
	 * @return bool|string
	 */
	public function sendSms(Lead $lead, $content)
	{

		$number = $lead->getLeadPhoneNumber();

		if (empty($number)) {
			$this->logger->addWarning("No phone number.");
			return false;
		}

		try {
			$this->configureClient();

			$message = [
				'to' => $lead->getLeadPhoneNumber(),
				'contents' => array(
					[
						"type" => "text",
						"text" => $content
					]
				),
			];

			try {
				$log = $this->client->post($message);
			} catch (Throwable $e) {
				$this->logger->addWarning($log);
				$this->logger->addWarning($e->getMessage());
			}

			return true;
		} catch (NumberParseException $exception) {

			$this->logger->addWarning(
				$exception->getMessage(),
				['exception' => $exception]
			);

			return $exception->getMessage();
		} catch (Exception $exception) {
			$this->logger->addWarning(
				$exception->getMessage(),
				['exception' => $exception]
			);

			return $exception->getMessage();
		}
	}

	/**
	 * @param string $number
	 *
	 * @return string
	 *
	 * @throws NumberParseException
	 */
	private function sanitizeNumber($number)
	{
		$util = PhoneNumberUtil::getInstance();
		$parsed = $util->parse($number, 'KE');

		return $util->format($parsed, PhoneNumberFormat::E164);
	}

	private function configureClient()
	{
		if ($this->client) {
			// Already configured
			return;
		}

		$this->client = new PowerticSmsClient(
			$this->configuration->getApiKey(),
			$this->configuration->getUrl()
		);
	}
}
