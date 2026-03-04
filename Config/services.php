<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use MauticPlugin\PowerticSmsBundle\Core\Configuration;
use MauticPlugin\PowerticSmsBundle\Core\PowerticSmsTransport;
use MauticPlugin\PowerticSmsBundle\Integration\PowerticSmsIntegration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
	$services = $configurator->services()
		->defaults()
		->autowire()
		->autoconfigure()
		->public();

	$excludes = [
		'Core/PowerticSmsClient.php',
		'Core/Transport/*',  // Internal transport implementations (not services)
	];

	$services->load('MauticPlugin\\PowerticSmsBundle\\', '../')
		->exclude('../{' . implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)) . '}');

	// Configuration service
	$services->alias('mautic.sms.powerticsms.configuration', Configuration::class);

	// Transport service with SMS transport tag
	$services->get(PowerticSmsTransport::class)
		->tag('mautic.sms_transport', [
			'alias'            => 'mautic.sms.transport.powerticsms',
			'integrationAlias' => 'PowerticSms',
		]);

	$services->alias('mautic.sms.transport.powerticsms', PowerticSmsTransport::class);

	// Integration
	$services->alias('mautic.integration.powerticsms', PowerticSmsIntegration::class);
};
