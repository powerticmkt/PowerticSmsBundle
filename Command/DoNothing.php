<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: DoNothing::COMMAND, description: 'A placeholder command that does nothing.')]
class DoNothing extends Command
{
	public const COMMAND = 'mautic:powerticsms:donothing';

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('I do nothing. And I look great doing it. (•̀ᴗ•́)و ̑̑');

		return Command::SUCCESS;
	}
}
