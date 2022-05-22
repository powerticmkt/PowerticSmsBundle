<?php declare (strict_types = 1);

namespace MauticPlugin\PowerticSmsBundle\Tests\Functional\Command;

use MauticPlugin\PowerticSmsBundle\Command\DoNothing;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TasksManageTest extends KernelTestCase {
	/**
	 * @var CommandTester
	 */
	private $commandTester;

	protected function setUp(): void{
		parent::setUp();

		$application = new Application(static::bootKernel());
		$this->commandTester = new CommandTester($application->find(DoNothing::COMMAND));
	}

	public function testCommandExecution(): void{
		$this->commandTester->execute([]);

		$this->assertSame("I do nothing. And I look great doing it. (•̀ᴗ•́)و ̑̑\n", $this->commandTester->getDisplay());
		$this->assertSame(0, $this->commandTester->getStatusCode());
	}
}
