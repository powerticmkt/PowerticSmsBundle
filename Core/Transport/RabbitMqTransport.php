<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core\Transport;

use Mautic\LeadBundle\Entity\Lead;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * RabbitMQ transport for sending SMS via AMQP message queue.
 */
class RabbitMqTransport implements TransportInterface
{
	private ?AMQPStreamConnection $connection = null;
	private ?AMQPChannel $channel = null;

	public function __construct(
		private string $host,
		private int $port,
		private string $user,
		private string $password,
		private string $vhost,
		private string $queue,
		private string $exchange,
		private string $routingKey,
		private LoggerInterface $logger,
	) {}

	public function __destruct()
	{
		$this->closeConnection();
	}

	public function send(Lead $lead, string $content): bool|string
	{
		try {
			$this->connect();

			$payload = $this->buildPayload($lead, $content);
			$this->publish($payload);

			return true;
		} catch (Throwable $e) {
			$this->logger->warning($e->getMessage(), ['exception' => $e]);

			return $e->getMessage();
		}
	}

	/**
	 * Build the full payload with lead data.
	 *
	 * @return array<string, mixed>
	 */
	private function buildPayload(Lead $lead, string $content): array
	{
		return [
			'to'        => $lead->getLeadPhoneNumber(),
			'contents'  => [
				[
					'type' => 'text',
					'text' => $content,
				],
			],
			'contact'   => $this->getLeadData($lead),
			'timestamp' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s.uP'),
		];
	}

	/**
	 * Get all lead/contact data as array.
	 *
	 * @return array<string, mixed>
	 */
	private function getLeadData(Lead $lead): array
	{
		$fields = $lead->getFields(true);
		$leadData = [
			'id'             => $lead->getId(),
			'points'         => $lead->getPoints(),
			'color'          => $lead->getColor(),
			'title'          => $lead->getTitle(),
			'firstname'      => $lead->getFirstname(),
			'lastname'       => $lead->getLastname(),
			'company'        => $lead->getCompany(),
			'position'       => $lead->getPosition(),
			'email'          => $lead->getEmail(),
			'phone'          => $lead->getLeadPhoneNumber(),
			'mobile'         => $lead->getMobile(),
			'address1'       => $lead->getAddress1(),
			'address2'       => $lead->getAddress2(),
			'city'           => $lead->getCity(),
			'state'          => $lead->getState(),
			'zipcode'        => $lead->getZipcode(),
			'country'        => $lead->getCountry(),
			'preferred_locale' => $lead->getPreferredLocale(),
			'attribution_date' => $lead->getFieldValue('attribution_date'),
			'attribution'    => $lead->getAttribution(),
			'timezone'       => $lead->getTimezone(),
			'owner_id'       => $lead->getOwner()?->getId(),
			'stage_id'       => $lead->getStage()?->getId(),
			'date_added'     => $lead->getDateAdded()?->format('Y-m-d H:i:s'),
			'date_modified'  => $lead->getDateModified()?->format('Y-m-d H:i:s'),
			'date_identified' => $lead->getDateIdentified()?->format('Y-m-d H:i:s'),
			'last_active'    => $lead->getLastActive()?->format('Y-m-d H:i:s'),
			'created_by'     => $lead->getCreatedBy(),
			'created_by_user' => $lead->getCreatedByUser(),
			'modified_by'    => $lead->getModifiedBy(),
			'modified_by_user' => $lead->getModifiedByUser(),
		];

		// Add all custom fields
		foreach ($fields as $group => $groupFields) {
			foreach ($groupFields as $alias => $fieldData) {
				if (!isset($leadData[$alias])) {
					$leadData[$alias] = $fieldData['value'] ?? null;
				}
			}
		}

		// Add tags
		$tags = [];
		foreach ($lead->getTags() as $tag) {
			$tags[] = $tag->getTag();
		}
		$leadData['tags'] = $tags;

		// Add UTM tags if available
		$utmTags = $lead->getUtmTags();
		if ($utmTags && $utmTags->count() > 0) {
			$latestUtm = $utmTags->last();
			$leadData['utm'] = [
				'campaign' => $latestUtm->getUtmCampaign(),
				'content'  => $latestUtm->getUtmContent(),
				'medium'   => $latestUtm->getUtmMedium(),
				'source'   => $latestUtm->getUtmSource(),
				'term'     => $latestUtm->getUtmTerm(),
			];
		}

		return $leadData;
	}

	/**
	 * Publish message to RabbitMQ.
	 *
	 * @param array<string, mixed> $payload
	 */
	private function publish(array $payload): void
	{
		$message = new AMQPMessage(
			json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
			[
				'content_type'  => 'application/json',
				'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
				'timestamp'     => time(),
			]
		);

		$exchange = $this->exchange ?: '';
		$routingKey = $this->routingKey ?: $this->queue;

		$this->channel->basic_publish($message, $exchange, $routingKey);

		$this->logger->info('SMS message published to RabbitMQ', [
			'queue'       => $this->queue,
			'exchange'    => $exchange,
			'routing_key' => $routingKey,
		]);
	}

	/**
	 * Establish connection to RabbitMQ.
	 */
	private function connect(): void
	{
		if (null !== $this->connection && $this->connection->isConnected()) {
			return;
		}

		$this->connection = new AMQPStreamConnection(
			$this->host,
			$this->port,
			$this->user,
			$this->password,
			$this->vhost
		);

		$this->channel = $this->connection->channel();

		// Declare queue to ensure it exists
		$this->channel->queue_declare(
			$this->queue,
			false, // passive
			true,  // durable
			false, // exclusive
			false  // auto_delete
		);

		// If exchange is specified, declare and bind
		if (!empty($this->exchange)) {
			$this->channel->exchange_declare(
				$this->exchange,
				'direct',
				false, // passive
				true,  // durable
				false  // auto_delete
			);

			$this->channel->queue_bind(
				$this->queue,
				$this->exchange,
				$this->routingKey ?: $this->queue
			);
		}
	}

	/**
	 * Close RabbitMQ connection.
	 */
	private function closeConnection(): void
	{
		try {
			if (null !== $this->channel) {
				$this->channel->close();
			}
			if (null !== $this->connection) {
				$this->connection->close();
			}
		} catch (Throwable $e) {
			$this->logger->warning('Error closing RabbitMQ connection: ' . $e->getMessage());
		}
	}
}
