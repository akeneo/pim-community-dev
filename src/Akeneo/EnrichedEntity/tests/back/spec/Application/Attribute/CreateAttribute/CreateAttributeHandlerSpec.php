<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\ExistsAttributeInterface;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAttributeHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $repository,
        AttributeFactoryRegistryInterface $registry,
        ExistsAttributeInterface $existsAttribute
    ) {
        $this->beConstructedWith($registry, $repository, $existsAttribute);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAttributeHandler::class);
    }

    function it_creates_an_attribute_with_the_factory_and_adds_it_to_the_repository(
        AttributeFactoryRegistryInterface $registry,
        AttributeRepositoryInterface $repository,
        AttributeFactoryInterface $factory,
        ExistsAttributeInterface $existsAttribute
    ) {
        $textAttribute = $this->getAttribute();
        $textCommand = new CreateTextAttributeCommand();
        $textCommand->identifier['enriched_entity_identifier'] = 'designer';
        $textCommand->identifier['identifier'] = 'name';
        $textCommand->order = 3;

        $existsAttribute->withIdentifier(Argument::cetera())->willReturn(false);
        $existsAttribute->withEnrichedEntityIdentifierAndOrder(Argument::cetera())->willReturn(false);
        $registry->getFactory($textCommand)->willReturn($factory);
        $factory->create($textCommand)->willReturn($textAttribute);
        $repository->create($textAttribute)->shouldBeCalled();

        $this->__invoke($textCommand);
    }

    function it_throws_if_the_enriched_entity_already_has_this_attribute_code(
        AttributeRepositoryInterface $repository,
        ExistsAttributeInterface $existsAttribute
    ) {
        $textAttribute = $this->getAttribute();
        $textCommand = new CreateTextAttributeCommand();
        $textCommand->identifier['enriched_entity_identifier'] = 'designer';
        $textCommand->identifier['identifier'] = 'name';

        $existsAttribute->withIdentifier(Argument::cetera())->willReturn(true);
        $repository->create($textAttribute)->shouldNotBeCalled();

        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textCommand]);
    }

    function it_throws_if_attribute_order_is_already_taken_by_another_attribute(
        AttributeRepositoryInterface $repository,
        ExistsAttributeInterface $existsAttribute
    ) {
        $textAttribute = $this->getAttribute();
        $textCommand = new CreateTextAttributeCommand();
        $textCommand->identifier['enriched_entity_identifier'] = 'designer';
        $textCommand->identifier['identifier'] = 'name';
        $textCommand->order = 5;

        $existsAttribute->withIdentifier(Argument::cetera())->willReturn(false);
        $existsAttribute->withEnrichedEntityIdentifierAndOrder(Argument::cetera())->willReturn(true);
        $repository->create($textAttribute)->shouldNotBeCalled();

        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textCommand]);
    }

    private function getAttribute(): TextAttribute
    {
        return TextAttribute::create(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
        );
    }
}
