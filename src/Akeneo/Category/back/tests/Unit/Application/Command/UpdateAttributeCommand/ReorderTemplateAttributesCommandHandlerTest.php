<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command\UpdateAttributeCommand;

use Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand\ReorderTemplateAttributesCommand;
use Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand\ReorderTemplateAttributesCommandHandler;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesCommandHandlerTest extends TestCase
{
    public function testItReorderTemplateAttributes(): void
    {
        $updateCategoryTemplateAttributesOrder = $this->createMock(UpdateCategoryTemplateAttributesOrder::class);

        $attributeUuids = [
            'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
            '850faf3f-458e-4e52-a7fd-ba587b081bee',
        ];

        $command = ReorderTemplateAttributesCommand::create($attributeUuids);

        $handler = new ReorderTemplateAttributesCommandHandler($updateCategoryTemplateAttributesOrder);

        $updateCategoryTemplateAttributesOrder
            ->expects($this->once())
            ->method('fromAttributeUuids')
            ->with($attributeUuids);

        $handler($command);
    }
}
