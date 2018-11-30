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
        $this->identifier = 'designer_starck_fingerprint';
        $this->code = 'starck';
        $this->referenceEntityIdentifier = 'designer';
        $this->labels = [
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ];
        $this->image = null;
        $this->values = [
            'designer_name_fingerprint_en_US' => [
                'attribute' => 'designer_name_fingerprint',
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'A nice name'
            ]
        ];

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
                'values' => [
                    'designer_name_fingerprint_en_US' => [
                        'attribute' => 'designer_name_fingerprint',
                        'channel' => null,
                        'locale' => 'en_US',
                        'data' => 'A nice name'
                    ]
                ],
                'completeness_percentage' => null
            ]
        );
    }
}
