<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindSearchableAssets;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetNormalizerSpec extends ObjectBehavior
{
    function let(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $this->beConstructedWith(
            $findValueKeysToIndexForAllChannelsAndLocales,
            $findSearchableAssets,
            $findValueKeysByAttributeType
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetNormalizer::class);
    }

    function it_normalizes_a_searchable_asset_by_asset_identifier(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $assetIdentifier = AssetIdentifier::fromString('stark');
        $stark = new SearchableAssetItem();
        $stark->identifier = 'designer_stark_fingerprint';
        $stark->assetFamilyIdentifier = 'designer';
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
        $findSearchableAssets
            ->byAssetIdentifier($assetIdentifier)
            ->willReturn($stark);

        $findValueKeysToIndexForAllChannelsAndLocales->find(Argument::type(AssetFamilyIdentifier::class))
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
                AssetFamilyIdentifier::fromString($stark->assetFamilyIdentifier),
                ['option', 'option_collection']
            )
            ->willReturn([$stark->assetFamilyIdentifier]);

        $normalizedAsset = $this->normalizeAsset($assetIdentifier);
        $normalizedAsset['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedAsset['code']->shouldBeEqualTo('stark');
        $normalizedAsset['asset_family_code']->shouldBeEqualTo('designer');
        $normalizedAsset['asset_full_text_search']->shouldBeEqualTo([
                'ecommerce' => [
                    'fr_FR' => "stark Bio",
                ],
                'mobile'    => [
                    'en_US' => "stark Bio",
                ],
            ]
        );
        $normalizedAsset['complete_value_keys']->shouldBeEqualTo([
                'name'                     => true,
                'description_mobile_en_US' => true,
            ]
        );
        $normalizedAsset['updated_at']->shouldBeInt();
    }

    function it_normalizes_a_searchable_assets_by_asset_family(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        \Iterator $searchableAssetItemIterator
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $stark = new SearchableAssetItem();
        $stark->identifier = 'designer_stark_fingerprint';
        $stark->assetFamilyIdentifier = 'designer';
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

        $coco = new SearchableAssetItem();
        $coco->identifier = 'designer_coco_fingerprint';
        $coco->assetFamilyIdentifier = 'designer';
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
        $findSearchableAssets
            ->byAssetFamilyIdentifier($assetFamilyIdentifier)
            ->willReturn($searchableAssetItemIterator);
        $searchableAssetItemIterator->valid()->willReturn(true, true, false);
        $searchableAssetItemIterator->current()->willReturn($stark, $coco);

        $findValueKeysToIndexForAllChannelsAndLocales->find(Argument::type(AssetFamilyIdentifier::class))
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

        $this->normalizeAssetsByAssetFamily($assetFamilyIdentifier);
    }
}
