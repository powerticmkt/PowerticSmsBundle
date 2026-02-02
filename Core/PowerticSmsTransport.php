<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core;

use Exception;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Sms\TransportInterface;
use MauticPlugin\PowerticSmsBundle\Core\Transport\HttpTransport;
use MauticPlugin\PowerticSmsBundle\Core\Transport\RabbitMqTransport;
use MauticPlugin\PowerticSmsBundle\Core\Transport\TransportInterface as InternalTransportInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Main SMS Transport that delegates to HTTP or RabbitMQ based on configuration.
 */
class PowerticSmsTransport implements TransportInterface
{
	private ?InternalTransportInterface $transport = null;

	public function __construct(
		private Configuration $configuration,
		private LoggerInterface $logger,
	) {}

	/**
	 * @param string $content
	 *
	 * @return bool|string
	 */
	public function sendSms(Lead $lead, $content)
	{
		$number = $lead->getLeadPhoneNumber();

		if (empty($number)) {
			$this->logger->warning('PowerticSms: No phone number for lead.', [
				'lead_id' => $lead->getId(),
			]);

			return false;
		}

		try {
			$transport = $this->getTransport();

			$this->logger->info('PowerticSms: Sending SMS via ' . $this->configuration->getTransportType(), [
				'lead_id'        => $lead->getId(),
				'phone'          => $number,
				'transport_type' => $this->configuration->getTransportType(),
			]);

			return $transport->send($lead, $content);
		} catch (Throwable $exception) {
			$this->logger->error('PowerticSms: Failed to send SMS', [
				'lead_id'   => $lead->getId(),
				'exception' => $exception->getMessage(),
			]);

			return $exception->getMessage();
		}
	}

	/**
	 * Get or create the appropriate transport based on configuration.
	 *
	 * @throws Exception
	 */
	private function getTransport(): InternalTransportInterface
	{
		if (null !== $this->transport) {
			return $this->transport;
		}

		$transportType = $this->configuration->getTransportType();

		$this->transport = match ($transportType) {
			Configuration::TRANSPORT_HTTP     => $this->createHttpTransport(),
			Configuration::TRANSPORT_RABBITMQ => $this->createRabbitMqTransport(),
			default => throw new Exception("Unknown transport type: {$transportType}"),
		};

		return $this->transport;
	}

	/**
	 * Create HTTP transport instance.
	 */
	private function createHttpTransport(): HttpTransport
	{
		return new HttpTransport(
			$this->configuration->getApiKey(),
			$this->configuration->getUrl(),
			$this->logger
		);
	}

	/**
	 * Create RabbitMQ transport instance.
	 */
	private function createRabbitMqTransport(): RabbitMqTransport
	{
		return new RabbitMqTransport(
			$this->configuration->getRabbitMqHost(),
			$this->configuration->getRabbitMqPort(),
			$this->configuration->getRabbitMqUser(),
			$this->configuration->getRabbitMqPassword(),
			$this->configuration->getRabbitMqVhost(),
			$this->configuration->getRabbitMqQueue(),
			$this->configuration->getRabbitMqExchange(),
			$this->configuration->getRabbitMqRoutingKey(),
			$this->logger
		);
	}
}
