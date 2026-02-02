<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\EventSubscriber;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\MenuEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds SMS menu item when PowerticSms integration is enabled.
 */
class MenuSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private IntegrationHelper $integrationHelper,
	) {}

	/**
	 * @return array<string, array{0: string, 1: int}>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			CoreEvents::BUILD_MENU => ['onBuildMenu', 9998],
		];
	}

	public function onBuildMenu(MenuEvent $event): void
	{
		if ('main' !== $event->getType()) {
			return;
		}

		// Check if PowerticSms integration is enabled
		if (!$this->isPowerticSmsEnabled()) {
			return;
		}

		// Add SMS menu item under Channels
		$event->addMenuItems(
			[
				'priority' => 70,
				'items'    => [
					'mautic.sms.smses' => [
						'id'        => 'mautic_sms_index',
						'route'     => 'mautic_sms_index',
						'access'    => ['sms:smses:viewown', 'sms:smses:viewother'],
						'parent'    => 'mautic.core.channels',
						'iconClass' => '',
						'priority'  => 70,
					],
				],
			]
		);
	}

	/**
	 * Check if PowerticSms integration is published and configured.
	 */
	private function isPowerticSmsEnabled(): bool
	{
		try {
			$integration = $this->integrationHelper->getIntegrationObject('PowerticSms');

			if (null === $integration) {
				return false;
			}

			$settings = $integration->getIntegrationSettings();

			return $settings && $settings->getIsPublished();
		} catch (\Exception $e) {
			return false;
		}
	}
}
