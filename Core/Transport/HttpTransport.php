<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core\Transport;

use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\PowerticSmsBundle\Core\PowerticSmsClient;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * HTTP Webhook transport for sending SMS via HTTP POST.
 */
class HttpTransport implements TransportInterface
{
	private ?PowerticSmsClient $client = null;

	public function __construct(
		private string $apiKey,
		private string $url,
		private LoggerInterface $logger,
	) {}

	public function send(Lead $lead, string $content): bool|string
	{
		try {
			$this->configureClient();

			$payload = $this->buildPayload($lead, $content);

			$this->client->post($payload);

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
			'to'       => $lead->getLeadPhoneNumber(),
			'contents' => [
				[
					'type' => 'text',
					'text' => $content,
				],
			],
			'contact'  => $this->getLeadData($lead),
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

	private function configureClient(): void
	{
		if (null !== $this->client) {
			return;
		}

		$this->client = new PowerticSmsClient(
			$this->apiKey,
			$this->url
		);
	}
}
