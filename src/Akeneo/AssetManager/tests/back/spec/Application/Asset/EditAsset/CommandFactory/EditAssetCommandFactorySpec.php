<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\SqlFindAttributesIndexedByIdentifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry,
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($editAssetValueCommandFactoryRegistry, $sqlFindAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetCommandFactory::class);
    }

    function it_creates_an_edit_asset_command_by_recursively_calling_other_edit_asset_value_factories(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry,
        EditValueCommandFactoryInterface $textValueCommandFactory,
        EditTextValueCommand $editDescriptionCommand
    ) {
        $normalizedCommand = [
            'asset_family_identifier' => 'designer',
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
        $descriptionAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'description', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
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
        $sqlFindAttributesIndexedByIdentifier->find(Argument::type(AssetFamilyIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute
        ]);

        $editAssetValueCommandFactoryRegistry->getFactory($descriptionAttribute, $normalizedCommand['values'][0])->willReturn($textValueCommandFactory);
        $textValueCommandFactory->create($descriptionAttribute, $normalizedCommand['values'][0])->willReturn($editDescriptionCommand);

        $command = $this->create($normalizedCommand);
        $command->shouldBeAnInstanceOf(EditAssetCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('philippe_starck');
        $command->labels->shouldBeEqualTo([]);
        $command->editAssetValueCommands[0]->shouldBeAnInstanceOf(EditTextValueCommand::class);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during('create', [['wrong_asset' => 'name']]);
    }

    function it_does_not_create_a_command_if_the_userinput_is_malformed(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry
    ) {
        $normalizedCommand = [
            'asset_family_identifier' => 'designer',
            'code' => 'philippe_starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [ [ 'malformed data']]
        ];

        $sqlFindAttributesIndexedByIdentifier->find(Argument::type(AssetFamilyIdentifier::class))->willReturn([]);
        $editAssetValueCommandFactoryRegistry->getFactory()->shouldNotBeCalled();
        $command = $this->create($normalizedCommand);
        $command->editAssetValueCommands->shouldBeEqualTo([]);
    }

    function it_does_not_create_a_command_if_the_attribute_does_not_exist(
        SqlFindAttributesIndexedByIdentifier $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry
    ) {
        $normalizedCommand = [
            'asset_family_identifier' => 'designer',
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
        $sqlFindAttributesIndexedByIdentifier->find(Argument::type(AssetFamilyIdentifier::class))->willReturn([]);
        $editAssetValueCommandFactoryRegistry->getFactory()->shouldNotBeCalled();
        $command = $this->create($normalizedCommand);
        $command->editAssetValueCommands->shouldBeEqualTo([]);
    }
}
