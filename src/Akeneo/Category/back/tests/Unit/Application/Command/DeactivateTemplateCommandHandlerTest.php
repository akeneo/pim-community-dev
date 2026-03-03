<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command;

use Akeneo\Category\Application\Command\DeactivateTemplateCommand;
use Akeneo\Category\Application\Command\DeactivateTemplateCommandHandler;
use Akeneo\Category\Application\Query\DeactivateTemplate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateCommandHandlerTest extends TestCase
{
    public function testItDeactivatesTemplate(): void
    {
        $deactivateTemplate = $this->createMock(DeactivateTemplate::class);
        $eventDispatcherInterface = $this->createMock(EventDispatcherInterface::class);
        $deactivateTemplate
            ->expects($this->once())
            ->method('execute')
            ->with('a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11');

        $handler = new DeactivateTemplateCommandHandler($deactivateTemplate, $eventDispatcherInterface);
        $command = DeactivateTemplateCommand::create('a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11');

        $handler($command);
    }
}
