<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommandFactory;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryRegistryInterface;
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
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindAttributesIndexedByIdentifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry,
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($editRecordValueCommandFactoryRegistry, $sqlFindAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordCommandFactory::class);
    }

    function it_creates_an_edit_record_command_by_recursively_calling_other_edit_record_value_factories(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry,
        EditValueCommandFactoryInterface $textValueCommandFactory
    ) {
        $normalizedCommand = [
            'enriched_entity_identifier' => 'designer',
            'code' => 'philippe_starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                [
                    'attribute' => 'desginer_description_fingerprint',
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'an awesome designer'
                ]
            ]
        ];
        $editDescriptionCommand = new EditTextValueCommand();
        $descriptionAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'description', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['fr_FR' => 'Description', 'en_US' => 'Description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $sqlFindAttributesIndexedByIdentifier->__invoke(Argument::type(EnrichedEntityIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute
        ]);

        $editRecordValueCommandFactoryRegistry->getFactory($descriptionAttribute)->willReturn($textValueCommandFactory);
        $textValueCommandFactory->create($descriptionAttribute, $normalizedCommand['values'][0])->willReturn($editDescriptionCommand);

        $command = $this->create($normalizedCommand);
        $command->shouldBeAnInstanceOf(EditRecordCommand::class);
        $command->enrichedEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('philippe_starck');
        $command->labels->shouldBeEqualTo([
            'en_us' => 'Philippe Starck'
        ]);
        $command->editRecordValueCommands->shouldBeEqualTo([$editDescriptionCommand]);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during('create', [['wrong_record' => 'name']]);
    }

    function it_does_not_create_a_command_if_the_userinput_is_malformed(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry
    ) {
        $normalizedCommand = [
            'enriched_entity_identifier' => 'designer',
            'code' => 'philippe_starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [ [ 'malformed data']]
        ];

        $sqlFindAttributesIndexedByIdentifier->__invoke(Argument::type(EnrichedEntityIdentifier::class))->willReturn([]);
        $editRecordValueCommandFactoryRegistry->getFactory()->shouldNotBeCalled();
        $command = $this->create($normalizedCommand);
        $command->editRecordValueCommands->shouldBeEqualTo([]);
    }

    function it_does_not_create_a_command_if_the_attribute_does_not_exist(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry
    ) {
        $normalizedCommand = [
            'enriched_entity_identifier' => 'designer',
            'code' => 'philippe_starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                [
                    'attribute' => 'unknown_attribute_type',
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'an awesome designer',
                ],
            ],
        ];
        $sqlFindAttributesIndexedByIdentifier->__invoke(Argument::type(EnrichedEntityIdentifier::class))->willReturn([]);
        $editRecordValueCommandFactoryRegistry->getFactory()->shouldNotBeCalled();
        $command = $this->create($normalizedCommand);
        $command->editRecordValueCommands->shouldBeEqualTo([]);
    }
}
