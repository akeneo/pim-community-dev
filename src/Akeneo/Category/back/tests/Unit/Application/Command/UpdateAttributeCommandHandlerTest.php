<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command;

use Akeneo\Category\Application\Command\UpdateAttributeCommand;
use Akeneo\Category\Application\Command\UpdateAttributeCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeCommandHandlerTest extends TestCase
{
    public function testItChangesAttributeToRichText(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $getAttribute = $this->createMock(GetAttribute::class);
        $categoryTemplateAttributeSaver = $this->createMock(CategoryTemplateAttributeSaver::class);

        $attributeUuid = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

        $command = UpdateAttributeCommand::create($attributeUuid, true);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willReturn(new ConstraintViolationList());

        $attribute = Attribute::fromType(
            type: new AttributeType(AttributeType::TEXTAREA),
            uuid: AttributeUuid::fromString($attributeUuid),
            code: new AttributeCode('test_attribute'),
            order: AttributeOrder::fromInteger(0),
            isRequired: AttributeIsRequired::fromBoolean(true),
            isScopable: AttributeIsScopable::fromBoolean(true),
            isLocalizable: AttributeIsLocalizable::fromBoolean(true),
            labelCollection: LabelCollection::fromArray([]),
            templateUuid: TemplateUuid::fromString('e1afd94c-62b1-4b70-bfb8-b8185e421b93'),
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $getAttribute
            ->expects($this->once())
            ->method('byUuids')
            ->with([$attributeUuid])
            ->willReturn(AttributeCollection::fromArray([$attribute]));

        $categoryTemplateAttributeSaver
            ->expects($this->once())
            ->method('update')
            ->with($attribute);

        $handler = new UpdateAttributeCommandHandler($validator, $getAttribute, $categoryTemplateAttributeSaver);

        $handler($command);
    }

    public function testItThrowsInvalidArgumentException(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $getAttribute = $this->createMock(GetAttribute::class);
        $categoryTemplateAttributeSaver = $this->createMock(CategoryTemplateAttributeSaver::class);

        $attributeUuid = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

        $command = UpdateAttributeCommand::create($attributeUuid, true);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willReturn(new ConstraintViolationList());

        $getAttribute
            ->expects($this->once())
            ->method('byUuids')
            ->with([$attributeUuid])
            ->willReturn(AttributeCollection::fromArray([]));

        $handler = new UpdateAttributeCommandHandler($validator, $getAttribute, $categoryTemplateAttributeSaver);

        $this->expectException(\InvalidArgumentException::class);
        $handler($command);
    }
}
