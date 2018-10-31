<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindValueKeysToIndexForChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SearchableRecordItem;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindSearchableRecords;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNormalizerSpec extends ObjectBehavior
{
    function let(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        FindValueKeysToIndexForChannelAndLocaleInterface $findValueKeysToIndexForChannelAndLocale,
        SqlFindSearchableRecords $findSearchableRecords
    ) {
        $this->beConstructedWith($findActivatedLocalesPerChannels, $findValueKeysToIndexForChannelAndLocale, $findSearchableRecords);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordNormalizer::class);
    }

    function it_normalizes_a_searchable_record_by_record_identifier(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        FindValueKeysToIndexForChannelAndLocaleInterface $findValueKeysToIndexForChannelAndLocale,
        SqlFindSearchableRecords $findSearchableRecords
    ) {
        $recordIdentifier = RecordIdentifier::fromString('stark');
        $stark = new SearchableRecordItem();
        $stark->identifier = 'designer_stark_fingerprint';
        $stark->referenceEntityIdentifier = 'designer';
        $stark->code = 'stark';
        $stark->labels = ['fr_FR' => 'Philippe Stark'];
        $stark->values = [
            'name' => [
                'data' => 'Bio',
            ],
            'description_mobile_en_US' => [
                'data' => 'Bio',
            ],
        ];
        $findSearchableRecords
            ->byRecordIdentifier($recordIdentifier)
            ->willReturn($stark);

        $findActivatedLocalesPerChannels
            ->__invoke()
            ->willReturn(
                ['ecommerce' => ['fr_FR'], 'mobile' => ['en_US']]
            );
        $findValueKeysToIndexForChannelAndLocale
            ->__invoke(
                Argument::type(ReferenceEntityIdentifier::class),
                Argument::type(ChannelIdentifier::class),
                Argument::type(LocaleIdentifier::class)
            )->willReturn(
                ValueKeyCollection::fromValueKeys([ValueKey::createFromNormalized('name')])
            );

        $normalizedRecord = $this->normalizeRecord($recordIdentifier);
        $normalizedRecord['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedRecord['code']->shouldBeEqualTo('stark');
        $normalizedRecord['reference_entity_code']->shouldBeEqualTo('designer');
        $normalizedRecord['record_full_text_search']->shouldBeEqualTo([
                'ecommerce' => [
                    'fr_FR' => "stark Philippe Stark Bio",
                ],
                'mobile'    => [
                    'en_US' => "stark  Bio",
                ],
            ]
        );
        $normalizedRecord['updated_at']->shouldBeInt();
     }

    function it_normalizes_a_searchable_records_by_reference_entity(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        FindValueKeysToIndexForChannelAndLocaleInterface $findValueKeysToIndexForChannelAndLocale,
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
            'name' => [
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
            'name' => [
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

        $findActivatedLocalesPerChannels
            ->__invoke()
            ->willReturn(
                ['ecommerce' => ['fr_FR'], 'mobile' => ['en_US']]
            );
        $findValueKeysToIndexForChannelAndLocale
            ->__invoke(
                Argument::type(ReferenceEntityIdentifier::class),
                Argument::type(ChannelIdentifier::class),
                Argument::type(LocaleIdentifier::class)
            )->willReturn(
                ValueKeyCollection::fromValueKeys([ValueKey::createFromNormalized('name')])
            );

        $this->normalizeRecordsByReferenceEntity($referenceEntityIdentifier);
    }
}

