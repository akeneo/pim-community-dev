<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit;

use Akeneo\Category\Application\Command\DeactivateTemplateCommand;
use Akeneo\Category\Application\Command\DeactivateTemplateCommandHandler;
use Akeneo\Category\Application\Query\DeactivateTemplate;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateCommandHandlerTest extends CategoryTestCase
{
    public function testItDeactivatesTemplate(): void
    {
        $deactivateTemplate = $this->createMock(DeactivateTemplate::class);
        $getTemplate = $this->createMock(GetTemplate::class);
        $eventDispatcherInterface = $this->createMock(EventDispatcherInterface::class);
        $deactivateTemplate
            ->expects($this->once())
            ->method('execute')
            ->with('a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11');

        $handler = new DeactivateTemplateCommandHandler($deactivateTemplate, $getTemplate, $eventDispatcherInterface);
        $command = DeactivateTemplateCommand::create('a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11');

        $handler($command);
    }
}
