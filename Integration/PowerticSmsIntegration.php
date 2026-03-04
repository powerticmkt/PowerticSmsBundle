<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\PowerticSmsBundle\Core\Configuration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PowerticSmsIntegration extends AbstractIntegration
{
	public function getName(): string
	{
		return 'PowerticSms';
	}

	public function getIcon(): string
	{
		return 'plugins/PowerticSmsBundle/Assets/img/icon.png';
	}

	public function getAuthenticationType(): string
	{
		return 'none';
	}

	/**
	 * @return array<string, string>
	 */
	public function getRequiredKeyFields(): array
	{
		return [
			'transport_type' => 'mautic.powerticsms.form.transport_type',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function getSecretKeys(): array
	{
		return [
			'apikey',
			'rabbitmq_password',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendToForm(&$builder, $data, $formArea): void
	{
		if ('keys' !== $formArea) {
			return;
		}

		/** @var FormBuilderInterface $builder */

		// Transport Type Selector
		$builder->add(
			'transport_type',
			ChoiceType::class,
			[
				'label'      => 'mautic.powerticsms.form.transport_type',
				'label_attr' => ['class' => 'control-label'],
				'required'   => true,
				'choices'    => [
					'mautic.powerticsms.transport.http'     => Configuration::TRANSPORT_HTTP,
					'mautic.powerticsms.transport.rabbitmq' => Configuration::TRANSPORT_RABBITMQ,
				],
				'attr'       => [
					'class'    => 'form-control',
					'onchange' => 'Mautic.togglePowerticSmsFields(this.value)',
				],
				'data'       => $data['transport_type'] ?? Configuration::TRANSPORT_HTTP,
			]
		);

		// === HTTP Configuration Fields ===
		$builder->add(
			'url',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.url',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-http-field',
					'placeholder' => 'https://api.example.com/sms/send',
				],
				'data'       => $data['url'] ?? '',
			]
		);

		$builder->add(
			'apikey',
			PasswordType::class,
			[
				'label'      => 'mautic.powerticsms.form.apikey',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'        => 'form-control powerticsms-http-field',
					'placeholder'  => 'Your API Token',
					'autocomplete' => 'off',
				],
				'data'       => $data['apikey'] ?? '',
			]
		);

		// === RabbitMQ Configuration Fields ===
		$builder->add(
			'rabbitmq_host',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_host',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => 'localhost',
				],
				'data'       => $data['rabbitmq_host'] ?? 'localhost',
			]
		);

		$builder->add(
			'rabbitmq_port',
			NumberType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_port',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => '5672',
				],
				'data'       => $data['rabbitmq_port'] ?? 5672,
			]
		);

		$builder->add(
			'rabbitmq_user',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_user',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => 'guest',
				],
				'data'       => $data['rabbitmq_user'] ?? 'guest',
			]
		);

		$builder->add(
			'rabbitmq_password',
			PasswordType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_password',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'        => 'form-control powerticsms-rabbitmq-field',
					'placeholder'  => 'guest',
					'autocomplete' => 'off',
				],
				'data'       => $data['rabbitmq_password'] ?? '',
			]
		);

		$builder->add(
			'rabbitmq_vhost',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_vhost',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => '/',
				],
				'data'       => $data['rabbitmq_vhost'] ?? '/',
			]
		);

		$builder->add(
			'rabbitmq_queue',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_queue',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => 'sms_messages',
				],
				'data'       => $data['rabbitmq_queue'] ?? 'sms_messages',
			]
		);

		$builder->add(
			'rabbitmq_exchange',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_exchange',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => 'mautic.powerticsms.form.rabbitmq_exchange.placeholder',
				],
				'data'       => $data['rabbitmq_exchange'] ?? '',
			]
		);

		$builder->add(
			'rabbitmq_routing_key',
			TextType::class,
			[
				'label'      => 'mautic.powerticsms.form.rabbitmq_routing_key',
				'label_attr' => ['class' => 'control-label'],
				'required'   => false,
				'attr'       => [
					'class'       => 'form-control powerticsms-rabbitmq-field',
					'placeholder' => 'mautic.powerticsms.form.rabbitmq_routing_key.placeholder',
				],
				'data'       => $data['rabbitmq_routing_key'] ?? '',
			]
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array<string, mixed>
	 */
	public function getFormSettings(): array
	{
		return [
			'requires_callback'      => false,
			'requires_authorization' => false,
		];
	}
}
