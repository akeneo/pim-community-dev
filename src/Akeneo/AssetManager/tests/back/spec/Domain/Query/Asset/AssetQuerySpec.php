<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PhpSpec\ObjectBehavior;

class AssetQuerySpec extends ObjectBehavior
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
            AssetFamilyIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleIdentifierCollection::fromNormalized([$normalizedQuery['locale']]),
            20,
            null,
            $normalizedQuery['filters']
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetQuery::class);
    }

    function it_creates_a_asset_query()
    {
        $this->shouldBeAnInstanceOf(AssetQuery::class);
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
