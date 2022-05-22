<?php

/**
 * @copyright   2022 Powertic. All rights reserved
 * @author      Luiz Eduardo Oliveira Fonseca <luizeof@gmail.com>
 *
 * @link        https://powertic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types=1);

return [
	'name' => 'PowerticSmsBundle',
	'description' => 'Send SMS Payload to an Custom Endpoint.',
	'version' => '1.0.0',
	'author' => 'Powertic',

	'services' => [
		'other' => [
			'mautic.sms.transport.powerticsms' => [
				'class' => \MauticPlugin\PowerticSmsBundle\Core\PowerticSmsTransport::class,
				'arguments' => [
					'mautic.sms.powerticsms.configuration',
					'monolog.logger.mautic',
				],
				'tag' => 'mautic.sms_transport',
				'tagArguments' => [
					'integrationAlias' => 'PowerticSms',
				],
				'serviceAliases' => [
					'sms_api',
					'mautic.sms.api',
				],
			],
			'mautic.sms.powerticsms.configuration' => [
				'class' => \MauticPlugin\PowerticSmsBundle\Core\Configuration::class,
				'arguments' => [
					'mautic.helper.integration',
				],
			],
		],
		'integrations' => [
			'mautic.integration.powerticsms' => [
				'class' => \MauticPlugin\PowerticSmsBundle\Integration\PowerticSmsIntegration::class,
				'tags' => [
					'mautic.integration',
					'mautic.config_integration',
				],
				'arguments' => [
					'event_dispatcher',
					'mautic.helper.cache_storage',
					'doctrine.orm.entity_manager',
					'session',
					'request_stack',
					'router',
					'translator',
					'logger',
					'mautic.helper.encryption',
					'mautic.lead.model.lead',
					'mautic.lead.model.company',
					'mautic.helper.paths',
					'mautic.core.model.notification',
					'mautic.lead.model.field',
					'mautic.plugin.model.integration_entity',
					'mautic.lead.model.dnc',
				],
			],
		],
	],
	'menu' => [
		'main' => [
			'items' => [
				'mautic.sms.smses' => [
					'route' => 'mautic_sms_index',
					'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
					'parent' => 'mautic.core.channels',
					'checks' => [
						'integration' => [
							'PowerticSms' => [
								'enabled' => true,
							],
						],
					],
					'priority' => 70,
				],
			],
		],
	],
	'parameters' => [
        'sms_enabled'              => false,
        'sms_username'             => null,
        'sms_password'             => null,
        'sms_sending_phone_number' => null,
        'sms_frequency_number'     => 0,
        'sms_frequency_time'       => 'DAY',
        'sms_transport'            => 'mautic.sms.twilio.transport',
    ],
];
