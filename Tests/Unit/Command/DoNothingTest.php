<?php declare (strict_types = 1);

namespace MauticPlugin\PowerticSmsBundle\Tests\Unit\Command;

use MauticPlugin\PowerticSmsBundle\Command\DoNothing;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DoNothingTest extends \PHPUnit\Framework\TestCase {
	/**
	 * @var InputInterface|MockObject
	 */
	private $input;

	/**
	 * @var OutputInterface|MockObject
	 */
	private $output;

	/**
	 * @var DoNothing
	 */
	private $command;

	protected function setUp(): void{
		$this->input = $this->createMock(InputInterface::class);
		$this->output = $this->createMock(OutputInterface::class);
		$this->command = new DoNothing();

		parent::setUp();
	}

	public function testSaveApiKeyEmpty(): void{
		$this->output->expects($this->once())
			->method('writeln')
			->with("I do nothing. And I look great doing it. (•̀ᴗ•́)و ̑̑");

		$this->assertSame(0, $this->command->run($this->input, $this->output));
	}
}
