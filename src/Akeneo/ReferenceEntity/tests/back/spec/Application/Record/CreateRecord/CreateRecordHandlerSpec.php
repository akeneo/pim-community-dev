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

namespace spec\Akeneo\ReferenceEntity\Application\Record\CreateRecord;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class CreateRecordHandlerSpec extends ObjectBehavior
{
    public function let(
        RecordRepositoryInterface $recordRepository,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ): void {
        $this->beConstructedWith($recordRepository, $findAttributeAsLabel);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateRecordHandler::class);
    }

    public function it_creates_and_save_a_new_record(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ): void {
        $createRecordCommand->code = 'intel';
        $createRecordCommand->referenceEntityIdentifier = 'brand';
        $createRecordCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $recordIdentifier = RecordIdentifier::fromString('brand_intel_a1677570-a278-444b-ab46-baa1db199392');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($createRecordCommand->referenceEntityIdentifier);
        $labelAttributeReference = AttributeAsLabelReference::createFromNormalized('label_brand_fingerprint');

        $findAttributeAsLabel
            ->find(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($labelAttributeReference);

        $recordRepository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(RecordCode::class)
        )->willReturn($recordIdentifier);

        $recordRepository->create(Argument::that(function ($record) use (
            $recordIdentifier,
            $referenceEntityIdentifier,
            $labelAttributeReference
        ) {
            $expectedRecord = Record::fromState(
                $recordIdentifier,
                $referenceEntityIdentifier,
                RecordCode::fromString('intel'),
                ValueCollection::fromValues([
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::createFromNormalize('Intel')
                    ),
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::createFromNormalize('Intel')
                    ),
                ]),
                $record->getCreatedAt(),
                $record->getUpdatedAt(),
            );

            Assert::eq($expectedRecord, $record);
            return true;
        }))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }

    public function it_creates_and_save_a_new_record_and_ignores_empty_labels(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ): void {
        $createRecordCommand->code = 'intel';
        $createRecordCommand->referenceEntityIdentifier = 'brand';
        $createRecordCommand->labels = [
            'en_US' => '',
            'fr_FR' => '',
        ];

        $recordIdentifier = RecordIdentifier::fromString('brand_intel_a1677570-a278-444b-ab46-baa1db199392');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($createRecordCommand->referenceEntityIdentifier);
        $labelAttributeReference = AttributeAsLabelReference::createFromNormalized('label_brand_fingerprint');

        $findAttributeAsLabel
            ->find(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($labelAttributeReference);

        $recordRepository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(RecordCode::class)
        )->willReturn($recordIdentifier);

        $recordRepository->create(Argument::that(function ($record) use (
            $recordIdentifier,
            $referenceEntityIdentifier,
            $labelAttributeReference
        ) {
            Assert::eq(Record::fromState(
                $recordIdentifier,
                $referenceEntityIdentifier,
                RecordCode::fromString('intel'),
                ValueCollection::fromValues([]),
                $record->getCreatedAt(),
                $record->getUpdatedAt(),
            ), $record);

            return true;
        }))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }

    public function it_creates_and_save_a_new_record_with_0_as_code_and_label(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ): void {
        $createRecordCommand->code = '0';
        $createRecordCommand->referenceEntityIdentifier = 'brand';
        $createRecordCommand->labels = [
            'en_US' => '0',
            'fr_FR' => 'zéro',
        ];

        $recordIdentifier = RecordIdentifier::fromString('brand_0_a1677570-a278-444b-ab46-baa1db199392');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($createRecordCommand->referenceEntityIdentifier);
        $labelAttributeReference = AttributeAsLabelReference::createFromNormalized('label_brand_fingerprint');

        $findAttributeAsLabel
            ->find(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($labelAttributeReference);

        $recordRepository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(RecordCode::class)
        )->willReturn($recordIdentifier);

        $recordRepository->create(Argument::that(function ($record) use (
            $recordIdentifier,
            $referenceEntityIdentifier,
            $labelAttributeReference
        ) {
            $expectedRecord = Record::fromState(
                $recordIdentifier,
                $referenceEntityIdentifier,
                RecordCode::fromString('0'),
                ValueCollection::fromValues([
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::createFromNormalize('0')
                    ),
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::createFromNormalize('zéro')
                    ),
                ]),
                $record->getCreatedAt(),
                $record->getUpdatedAt(),
            );

            Assert::eq($expectedRecord, $record);
            return true;
        }))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }
}
