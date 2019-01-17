<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditRecordCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditValueCommandFactoryRegistryInterface $editRecordValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($editRecordValueCommandFactoryRegistry, $findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordCommandFactory::class);
    }

    function it_creates_an_edit_record_command(
        $editRecordValueCommandFactoryRegistry,
        $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryInterface $textValueCommandFactory,
        TextAttribute $descriptionAttribute
    ) {
        $normalizedRecord = [
            'code' => 'starck',
            'image' => 'images/starck.jpg',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                'description' => [
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'en_US',
                        'data'      => 'an awesome designer'
                    ],
                ],
            ],
        ];
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $editDescriptionCommand = new EditTextValueCommand();

        $findAttributesIndexedByIdentifier->__invoke(Argument::type(ReferenceEntityIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute
        ]);
        $descriptionAttribute->getCode()->willReturn(AttributeCode::fromString('description'));

        $editRecordValueCommandFactoryRegistry->getFactory($descriptionAttribute, $normalizedRecord['values']['description'][0])
            ->willReturn($textValueCommandFactory);
        $textValueCommandFactory->create($descriptionAttribute, $normalizedRecord['values']['description'][0])
            ->willReturn($editDescriptionCommand);

        $command = $this->create($referenceEntityIdentifier, $normalizedRecord);
        $command->shouldBeAnInstanceOf(EditRecordCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('starck');
        $command->editRecordValueCommands->shouldBeEqualTo([$editDescriptionCommand]);
    }

    function it_creates_an_edit_record_command_without_values()
    {
        $normalizedRecord = [
            'code' => 'starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ]
        ];
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $command = $this->create($referenceEntityIdentifier, $normalizedRecord);
        $command->shouldBeAnInstanceOf(EditRecordCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBe('starck');
        $command->editRecordValueCommands->shouldBe([]);
    }

    function it_throws_an_exception_if_an_attribute_to_edit_does_not_exist(
        $findAttributesIndexedByIdentifier,
        TextAttribute $descriptionAttribute
    ) {
        $normalizedRecord = [
            'code' => 'starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                'wrong_attribute' => [
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => 'This attribute does not exist'
                ],
                'description' => [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'an awesome designer'
                ]
            ]
        ];
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $findAttributesIndexedByIdentifier->__invoke(Argument::type(ReferenceEntityIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute
        ]);
        $descriptionAttribute->getCode()->willReturn(AttributeCode::fromString('description'));

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [
            $referenceEntityIdentifier,
            $normalizedRecord
        ]);
    }
}
