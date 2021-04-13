<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchableRecordItem;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlFindSearchableRecords;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordNormalizerSpec extends ObjectBehavior
{
    function let(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableRecords $findSearchableRecords,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $this->beConstructedWith(
            $findValueKeysToIndexForAllChannelsAndLocales,
            $findSearchableRecords,
            $findValueKeysByAttributeType
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordNormalizer::class);
    }

    function it_normalizes_a_searchable_record_by_record_identifier(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableRecords $findSearchableRecords,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        \DateTimeImmutable $updatedAt
    ) {
        $recordIdentifier = RecordIdentifier::fromString('stark');
        $updatedAt->getTimestamp()->willReturn(1589524960);

        $stark = new SearchableRecordItem();
        $stark->identifier = 'designer_stark_fingerprint';
        $stark->referenceEntityIdentifier = 'designer';
        $stark->code = 'stark';
        $stark->labels = ['fr_FR' => 'Philippe Stark'];
        $stark->values = [
            'name'                     => [
                'data' => 'Bio',
            ],
            'description_mobile_en_US' => [
                'data' => 'Bio',
            ],
        ];
        $stark->updatedAt = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2020-05-15T10:16:21+0000');

        $findSearchableRecords
            ->byRecordIdentifier($recordIdentifier)
            ->willReturn($stark);

        $findValueKeysToIndexForAllChannelsAndLocales->find(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn(
                [
                    'ecommerce' => [
                        'fr_FR' => ['name'],
                    ],
                    'mobile'    => [
                        'en_US' => ['name'],
                    ],
                ]
            );
        $findValueKeysByAttributeType
            ->find(
                ReferenceEntityIdentifier::fromString($stark->referenceEntityIdentifier),
                ['option', 'option_collection', 'record', 'record_collection']
            )
            ->willReturn([$stark->referenceEntityIdentifier]);

        $normalizedRecord = $this->normalizeRecord($recordIdentifier);
        $normalizedRecord['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedRecord['code']->shouldBeEqualTo('stark');
        $normalizedRecord['reference_entity_code']->shouldBeEqualTo('designer');
        $normalizedRecord['record_full_text_search']->shouldBeEqualTo(
            [
                'ecommerce' => [
                    'fr_FR' => "stark Bio",
                ],
                'mobile'    => [
                    'en_US' => "stark Bio",
                ],
            ]
        );
        $normalizedRecord['complete_value_keys']->shouldBeEqualTo(
            [
                'name'                     => true,
                'description_mobile_en_US' => true,
            ]
        );

        $normalizedRecord['updated_at']->shouldBeEqualTo(1589537781);
    }

    function it_normalizes_a_searchable_records_by_reference_entity(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableRecords $findSearchableRecords,
        \Iterator $searchableRecordItemIterator
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $stark = new SearchableRecordItem();
        $stark->identifier = 'designer_stark_fingerprint';
        $stark->referenceEntityIdentifier = 'designer';
        $stark->code = 'stark';
        $stark->labels = ['fr_FR' => 'Philippe Stark'];
        $stark->values = [
            'name'                     => [
                'data' => 'starck Bio',
            ],
            'description_mobile_en_US' => [
                'data' => 'Bio',
            ],
        ];

        $coco = new SearchableRecordItem();
        $coco->identifier = 'designer_coco_fingerprint';
        $coco->referenceEntityIdentifier = 'designer';
        $coco->code = 'coco';
        $coco->labels = ['fr_FR' => 'Coco Chanel'];
        $coco->values = [
            'name'                     => [
                'data' => 'Coco bio',
            ],
            'description_mobile_en_US' => [
                'data' => 'bio',
            ],
        ];
        $findSearchableRecords
            ->byReferenceEntityIdentifier($referenceEntityIdentifier)
            ->willReturn($searchableRecordItemIterator);
        $searchableRecordItemIterator->valid()->willReturn(true, true, false);
        $searchableRecordItemIterator->current()->willReturn($stark, $coco);

        $findValueKeysToIndexForAllChannelsAndLocales->find(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn(
                [
                    'ecommerce' => [
                        'fr_FR' => ['name'],
                    ],
                    'mobile'    => [
                        'en_US' => ['name'],
                    ],
                ]
            );

        $this->normalizeRecordsByReferenceEntity($referenceEntityIdentifier);
    }
}
