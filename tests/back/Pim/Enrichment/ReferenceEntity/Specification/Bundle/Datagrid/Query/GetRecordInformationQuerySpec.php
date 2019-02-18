<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Query;

use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Query\GetRecordInformationQuery;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetRecordInformationQuerySpec extends ObjectBehavior
{
    function let(FindRecordDetailsInterface $findRecordDetails)
    {
        $this->beConstructedWith($findRecordDetails);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetRecordInformationQuery::class);
    }

    function it_gets_a_record_information_for_a_given_reference_entity_identifier_and_record_code($findRecordDetails)
    {
        $findRecordDetails->__invoke(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return $referenceEntityIdentifier->equals(ReferenceEntityIdentifier::fromString('designer'));
            }),
            Argument::that(function (RecordCode $recordCode) {
                return $recordCode->equals(RecordCode::fromString('stark'));
            })
        )->willReturn(
            new RecordDetails(
                RecordIdentifier::fromString('starck_designer_fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                LabelCollection::fromArray(['fr_FR' => 'Philippe Stark', 'en_US' => 'Philippe Stark']),
                Image::createEmpty(),
                [],
                false
            )
        );
        /** @var RecordInformation $recordInformation */
        $recordInformation = $this->fetch(
            'designer',
            'stark'
        );

        $recordInformation->referenceEntityIdentifier->shouldBe('designer');
        $recordInformation->code->shouldBe('stark');
        $recordInformation->labels->shouldBe(['fr_FR' => 'Philippe Stark', 'en_US' => 'Philippe Stark']);
    }

    function it_throws_if_the_record_does_not_exist($findRecordDetails)
    {
        $findRecordDetails->__invoke(Argument::any(), Argument::any())->willReturn(null);
        $this->shouldThrow(\LogicException::class)->during('fetch', ['designer', 'stark']);
    }
}
