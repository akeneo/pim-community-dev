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
    function let(
        RecordRepositoryInterface $recordRepository,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $this->beConstructedWith($recordRepository, $findAttributeAsLabel);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateRecordHandler::class);
    }

    function it_creates_and_save_a_new_record(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ) {
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
            ->__invoke(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($labelAttributeReference);

        $recordRepository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(RecordCode::class)
        )->willReturn($recordIdentifier);

        $expectedRecord = Record::create(
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
            ])
        );

        $recordRepository->create(Argument::that(function ($record) use ($expectedRecord) {
            Assert::eq($expectedRecord, $record);
            return true;
        }))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }
}
