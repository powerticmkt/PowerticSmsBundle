<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core\Transport;

use Mautic\LeadBundle\Entity\Lead;

/**
 * Interface for SMS transport implementations (HTTP, RabbitMQ, etc.)
 */
interface TransportInterface
{
	/**
	 * Send SMS message with full lead data.
	 *
	 * @param Lead   $lead    The lead entity with all contact information
	 * @param string $content The SMS message content
	 *
	 * @return bool|string True on success, error message on failure
	 */
	public function send(Lead $lead, string $content): bool|string;
}
