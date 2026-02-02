<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core;

use Exception;
use Mautic\PluginBundle\Helper\IntegrationHelper;

class Configuration
{
	public const TRANSPORT_HTTP = 'http';
	public const TRANSPORT_RABBITMQ = 'rabbitmq';

	private bool $configured = false;

	// Common
	private string $transportType = self::TRANSPORT_HTTP;

	// HTTP settings
	private ?string $url = null;
	private ?string $apikey = null;

	// RabbitMQ settings
	private ?string $rabbitmqHost = null;
	private int $rabbitmqPort = 5672;
	private ?string $rabbitmqUser = null;
	private ?string $rabbitmqPassword = null;
	private string $rabbitmqVhost = '/';
	private ?string $rabbitmqQueue = null;
	private ?string $rabbitmqExchange = null;
	private ?string $rabbitmqRoutingKey = null;

	public function __construct(
		private IntegrationHelper $integrationHelper,
	) {}

	/**
	 * @throws Exception
	 */
	public function getTransportType(): string
	{
		$this->setConfiguration();

		return $this->transportType;
	}

	/**
	 * Check if using HTTP transport.
	 */
	public function isHttpTransport(): bool
	{
		return $this->getTransportType() === self::TRANSPORT_HTTP;
	}

	/**
	 * Check if using RabbitMQ transport.
	 */
	public function isRabbitMqTransport(): bool
	{
		return $this->getTransportType() === self::TRANSPORT_RABBITMQ;
	}

	/**
	 * @throws Exception
	 */
	public function getUrl(): string
	{
		$this->setConfiguration();

		return $this->url ?? '';
	}

	/**
	 * @throws Exception
	 */
	public function getApiKey(): string
	{
		$this->setConfiguration();

		return $this->apikey ?? '';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqHost(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqHost ?? 'localhost';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqPort(): int
	{
		$this->setConfiguration();

		return $this->rabbitmqPort;
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqUser(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqUser ?? 'guest';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqPassword(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqPassword ?? 'guest';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqVhost(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqVhost;
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqQueue(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqQueue ?? 'sms_messages';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqExchange(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqExchange ?? '';
	}

	/**
	 * @throws Exception
	 */
	public function getRabbitMqRoutingKey(): string
	{
		$this->setConfiguration();

		return $this->rabbitmqRoutingKey ?? '';
	}

	/**
	 * @throws Exception
	 */
	private function setConfiguration(): void
	{
		if ($this->configured) {
			return;
		}

		$integration = $this->integrationHelper->getIntegrationObject('PowerticSms');

		if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
			throw new Exception('PowerticSms integration is not enabled.');
		}

		$keys = $integration->getDecryptedApiKeys();

		// Transport type (default to HTTP for backward compatibility)
		$this->transportType = $keys['transport_type'] ?? self::TRANSPORT_HTTP;

		if ($this->transportType === self::TRANSPORT_HTTP) {
			// Validate HTTP configuration
			if (empty($keys['url']) || empty($keys['apikey'])) {
				throw new Exception('PowerticSms HTTP configuration not set. Please configure URL and API Key.');
			}
			$this->url = $keys['url'];
			$this->apikey = $keys['apikey'];
		} elseif ($this->transportType === self::TRANSPORT_RABBITMQ) {
			// Validate RabbitMQ configuration
			if (empty($keys['rabbitmq_host']) || empty($keys['rabbitmq_queue'])) {
				throw new Exception('PowerticSms RabbitMQ configuration not set. Please configure Host and Queue.');
			}
			$this->rabbitmqHost = $keys['rabbitmq_host'];
			$this->rabbitmqPort = (int) ($keys['rabbitmq_port'] ?? 5672);
			$this->rabbitmqUser = $keys['rabbitmq_user'] ?? 'guest';
			$this->rabbitmqPassword = $keys['rabbitmq_password'] ?? 'guest';
			$this->rabbitmqVhost = $keys['rabbitmq_vhost'] ?? '/';
			$this->rabbitmqQueue = $keys['rabbitmq_queue'];
			$this->rabbitmqExchange = $keys['rabbitmq_exchange'] ?? '';
			$this->rabbitmqRoutingKey = $keys['rabbitmq_routing_key'] ?? '';
		} else {
			throw new Exception("Unknown transport type: {$this->transportType}");
		}

		$this->configured = true;
	}
}
