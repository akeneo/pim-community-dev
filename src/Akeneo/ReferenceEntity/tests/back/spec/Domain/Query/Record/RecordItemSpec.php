<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use PhpSpec\ObjectBehavior;

class RecordItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordItem::class);
    }

    function it_normalizes_a_read_model()
    {
        $this->identifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $this->code = RecordCode::fromString('starck');
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $this->labels = LabelCollection::fromArray([
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ]);
        $this->image = Image::createEmpty();

        $this->normalize()->shouldReturn(
            [
                'identifier'                 => 'designer_starck_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'starck',
                'labels'                     => [
                    'fr_FR' => 'Philippe starck',
                    'en_US' => 'Philip starck',
                ],
                'image' => null,
            ]
        );
    }
}
