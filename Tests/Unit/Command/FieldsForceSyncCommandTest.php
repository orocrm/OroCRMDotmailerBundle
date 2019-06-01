<?php

namespace Oro\Bundle\DotmailerBundle\Tests\Unit\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\DotmailerBundle\Command\FieldsForceSyncCommand;
use Oro\Bundle\DotmailerBundle\Model\SyncManager;
use Oro\Component\Testing\ClassExtensionTrait;
use Symfony\Component\Console\Command\Command;

class FieldsForceSyncCommandTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    /** @var SyncManager|\PHPUnit\Framework\MockObject\MockObject */
    private $syncManager;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var FieldsForceSyncCommand */
    private $command;

    public function setUp()
    {
        $this->syncManager = $this->createMock(SyncManager::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->command = new FieldsForceSyncCommand($this->syncManager, $this->registry);
    }

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testShouldImplementCronCommandInterface()
    {
        $this->assertClassImplements(CronCommandInterface::class, FieldsForceSyncCommand::class);
    }

    public function testShouldBeRunDaily()
    {
        self::assertEquals('0 1 * * *', $this->command->getDefaultDefinition());
    }
}
