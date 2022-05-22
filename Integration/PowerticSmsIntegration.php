<?php

/**
 * @copyright   2022 Powertic. All rights reserved
 * @author      Luiz Eduardo Oliveira Fonseca <luizeof@gmail.com>
 *
 * @link        https://powertic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\PowerticSmsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;

class PowerticSmsIntegration extends AbstractIntegration implements ConfigFormInterface
{

	use ConfigurationTrait;
	use DefaultConfigFormTrait;

	public function getName(): string
	{
		return 'PowerticSms';
	}

	public function getDisplayName(): string
	{
		return 'Powertic SMS';
	}

	public function getIcon(): string
	{
		return 'plugins/PowerticSmsBundle/Assets/img/icon.png';
	}

	  /**
     * @return bool
     */
    public function getDisableTrackableUrls()
    {
        return true;
    }

	public function getAuthenticationType(): string
	{
		return 'none';
	}

	public function getRequiredKeyFields()
	{
		return [
			'apikey' => 'mautic.powerticsms.form.apikey',
			'url' => 'mautic.powerticsms.form.url',
		];
	}
}
