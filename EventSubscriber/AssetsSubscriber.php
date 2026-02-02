<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\EventSubscriber;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomAssetsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Injects PowerticSms JavaScript assets for the integration configuration page.
 */
class AssetsSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private RequestStack $requestStack,
	) {}

	/**
	 * @return array<string, array{0: string, 1: int}>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			CoreEvents::VIEW_INJECT_CUSTOM_ASSETS => ['injectAssets', 0],
		];
	}

	public function injectAssets(CustomAssetsEvent $assetsEvent): void
	{
		// Only inject on Mautic admin pages
		if (!$this->isMauticAdministrationPage()) {
			return;
		}

		// Add the PowerticSms JavaScript for field toggling
		$assetsEvent->addScript('plugins/PowerticSmsBundle/Assets/js/powerticsms.js');
	}

	/**
	 * Returns true for routes that starts with /s/ (admin pages).
	 */
	private function isMauticAdministrationPage(): bool
	{
		$request = $this->requestStack->getCurrentRequest();
		if (null === $request) {
			return false;
		}

		return preg_match('/^\/s\//', $request->getPathInfo()) >= 1;
	}
}
