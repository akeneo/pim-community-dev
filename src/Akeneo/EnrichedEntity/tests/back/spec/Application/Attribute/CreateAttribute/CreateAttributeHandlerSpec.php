<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAttributeHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $repository,
        AttributeFactoryRegistryInterface $registry
    ) {
        $this->beConstructedWith($registry, $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAttributeHandler::class);
    }

    function it_creates_an_attribute_with_the_factory_and_adds_it_to_the_repository(
        AttributeFactoryRegistryInterface $registry,
        AttributeRepositoryInterface $repository,
        AttributeFactoryInterface $factory,
        AttributeIdentifier $identifier
    ) {
        $repository->nextIdentifier(
            Argument::type(EnrichedEntityIdentifier::class),
            Argument::type(AttributeCode::class)
        )->willReturn($identifier);

        $textAttribute = $this->getAttribute();
        $textCommand = new CreateTextAttributeCommand();
        $textCommand->enrichedEntityIdentifier = 'designer';
        $textCommand->code = 'name';
        $textCommand->order = 3;

        $registry->getFactory($textCommand)->willReturn($factory);
        $factory->create($textCommand, $identifier)->willReturn($textAttribute);
        $repository->create($textAttribute)->shouldBeCalled();

        $this->__invoke($textCommand);
    }

    private function getAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
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
