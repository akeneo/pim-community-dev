<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PhpSpec\ObjectBehavior;

class RecordQuerySpec extends ObjectBehavior
{
    public function let()
    {
        $normalizedQuery = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'filters' => [
                [
                    'field'    => 'full_text',
                    'operator' => '=',
                    'value'    => 'test'
                ],
                [
                    'field'    => 'values.main_color_designers_fingerprint',
                    'operator' => '=',
                    'value'    => 'blue'
                ]
            ],
            'page'    => 0,
            'size'    => 20
        ];

        $this->beConstructedThrough('createFromNormalized', [
            $normalizedQuery
        ]);

        $this->beConstructedThrough('createPaginatedQueryUsingSearchAfter', [
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleIdentifierCollection::fromNormalized([$normalizedQuery['locale']]),
            20,
            null,
            $normalizedQuery['filters']
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordQuery::class);
    }

    function it_creates_a_record_query()
    {
        $this->shouldBeAnInstanceOf(RecordQuery::class);
    }

    function it_can_get_filter()
    {
        $filter = [
            'field' => 'full_text',
            'operator' => '=',
            'value' => 'test'
        ];

        $this->getFilter('full_text')->shouldReturn($filter);
    }

    function it_can_get_filter_values()
    {
        $filters = [
            [
                'field' => 'values.main_color_designers_fingerprint',
                'operator' => '=',
                'value' => 'blue'
            ]
        ];

        $this->getValueFilters()->shouldReturn($filters);
    }

    function it_has_filter()
    {
        $this->hasFilter('full_text')->shouldReturn(true);
        $this->hasFilter('values.*')->shouldReturn(true);
        $this->hasFilter('completeness')->shouldReturn(false);
    }

    function it_can_be_normalized()
    {
        $normalizedQuery = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'filters' => [
                [
                    'field'    => 'full_text',
                    'operator' => '=',
                    'value'    => 'test'
                ],
                [
                    'field'    => 'values.main_color_designers_fingerprint',
                    'operator' => '=',
                    'value'    => 'blue'
                ]
            ],
            'page'    => 0,
            'size'    => 20
        ];

        $this->beConstructedThrough('createFromNormalized', [
            $normalizedQuery
        ]);

        $this->normalize()->shouldReturn($normalizedQuery);
    }
}
