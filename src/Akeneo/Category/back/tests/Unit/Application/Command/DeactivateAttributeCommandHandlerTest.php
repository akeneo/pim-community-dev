<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command;

use Akeneo\Category\Application\Command\DeactivateAttributeCommand;
use Akeneo\Category\Application\Command\DeactivateAttributeCommandHandler;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateAttributeCommandHandlerTest extends TestCase
{
    public function testItDeactivatesTemplate(): void
    {
        $deactivateAttribute = $this->createMock(DeactivateAttribute::class);
        $eventDispatcherInterface = $this->createMock(EventDispatcherInterface::class);
        $templateUuid = TemplateUuid::fromString('a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11');
        $attributeUuid = AttributeUuid::fromString('49be1ded-fc45-41d2-9f01-cab820d1de8a');
        $deactivateAttribute
            ->expects($this->once())
            ->method('execute')
            ->with($templateUuid, $attributeUuid);

        $handler = new DeactivateAttributeCommandHandler($deactivateAttribute, $eventDispatcherInterface);
        $command = DeactivateAttributeCommand::create($templateUuid->getValue(), $attributeUuid->getValue());

        $handler($command);
    }
}
