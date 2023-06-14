<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Application\MainIdentifier;

use Akeneo\Pim\Structure\Bundle\Application\MainIdentifier\ChangeMainIdentifierHandler;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\MainIdentifier;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeMainIdentifierHandlerSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository): void
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_a_main_identifier_handler(): void
    {
        $this->shouldHaveType(ChangeMainIdentifierHandler::class);
    }

    public function it_should_throw_exception_when_invoked_with_unknown_attribute(
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $attributeCode = MainIdentifier::fromString('unknown_attribute');
        $attributeRepository->findOneByIdentifier('unknown_attribute')
            ->shouldBeCalled()
            ->willReturn(null);
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $attributeCode,
            ]);
    }

    public function it_should_throw_exception_when_invoked_with_text_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $attributeCode = MainIdentifier::fromString('text_attribute');
        $attribute->getType()
            ->shouldBeCalled()
            ->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attributeRepository->findOneByIdentifier('text_attribute')
            ->shouldBeCalled()
            ->willReturn($attribute);
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $attributeCode,
            ]);
    }

    public function it_should_do_nothing_when_identifier_is_already_main(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $mainIdentifierAttribute
    ): void {
        $attributeCode = MainIdentifier::fromString('main_identifier');
        $mainIdentifierAttribute->getType()
            ->shouldBeCalled()
            ->willReturn(AttributeTypes::IDENTIFIER);
        $mainIdentifierAttribute->isMainIdentifier()
            ->shouldBeCalled()
            ->willReturn(true);
        $attributeRepository->findOneByIdentifier('main_identifier')
            ->shouldBeCalled()
            ->willReturn($mainIdentifierAttribute);

        $attributeRepository->updateMainIdentifier(Argument::any())
            ->shouldNotBeCalled();
        $this->__invoke($attributeCode);
    }

    public function it_should_update_main_identifier_with_new_identifier(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $newIdentifierAttribute
    ): void {
        $attributeCode = MainIdentifier::fromString('new_identifier');
        $newIdentifierAttribute->getType()
            ->shouldBeCalled()
            ->willReturn(AttributeTypes::IDENTIFIER);
        $newIdentifierAttribute->isMainIdentifier()
            ->shouldBeCalled()
            ->willReturn(false);
        $attributeRepository->findOneByIdentifier('new_identifier')
            ->shouldBeCalled()
            ->willReturn($newIdentifierAttribute);

        $attributeRepository->updateMainIdentifier($newIdentifierAttribute)
            ->shouldBeCalled();
        $this->__invoke($attributeCode);
    }
}
