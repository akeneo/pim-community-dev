<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAttributeHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $repository,
        AttributeFactoryRegistryInterface $registry,
        FindAttributeNextOrderInterface $attributeNextOrder
    ) {
        $this->beConstructedWith(
            $registry,
            $repository,
            $attributeNextOrder
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAttributeHandler::class);
    }

    function it_creates_an_attribute_with_the_factory_and_adds_it_to_the_repository(
        AttributeFactoryRegistryInterface $registry,
        AttributeRepositoryInterface $repository,
        AttributeFactoryInterface $factory,
        AttributeIdentifier $identifier,
        $attributeNextOrder
    ) {
        $repository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(AttributeCode::class)
        )->willReturn($identifier)
        ;

        $attributeNextOrder
            ->withReferenceEntityIdentifier(ReferenceEntityIdentifier::fromString('designer'))
            ->willReturn(AttributeOrder::fromInteger(0))
        ;

        $textAttribute = $this->getAttribute();
        $textCommand = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            155,
            false,
            false,
            AttributeValidationRule::NONE,
            AttributeRegularExpression::EMPTY
        );

        $registry->getFactory($textCommand)->willReturn($factory);
        $factory->create(
            $textCommand,
            $identifier,
            AttributeOrder::fromInteger(0)
        )->willReturn($textAttribute)
        ;
        $repository->create($textAttribute)->shouldBeCalled();

        $this->__invoke($textCommand);
    }

    private function getAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create(
                'designer',
                'name',
                'test'
            ),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }
}
