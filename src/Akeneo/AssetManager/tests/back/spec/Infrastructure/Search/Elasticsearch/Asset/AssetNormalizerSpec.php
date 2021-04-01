<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
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
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindActivatedLocalesInterface $findActivatedLocales
    ) {
        $this->beConstructedWith(
            $findValueKeysToIndexForAllChannelsAndLocales,
            $findSearchableAssets,
            $findValueKeysByAttributeType,
            $findActivatedLocales
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetNormalizer::class);
    }

    function it_normalizes_a_searchable_asset_by_asset_identifier(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindActivatedLocalesInterface $findActivatedLocales
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
            'tags' => [
                'data' => ['industrial', 'street furniture']
            ]
        ];
        $findSearchableAssets
            ->byAssetIdentifier($assetIdentifier)
            ->willReturn($stark);

        $findActivatedLocales->findAll()->shouldBeCalledOnce()->willReturn(['en_US', 'fr_FR', 'de_DE']);
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
            ->willReturn(['tags']);

        $normalizedAsset = $this->normalizeAsset($assetIdentifier);
        $normalizedAsset['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedAsset['code']->shouldBeEqualTo('stark');
        $normalizedAsset['asset_family_code']->shouldBeEqualTo('designer');
        $normalizedAsset['asset_family_code']->shouldBeEqualTo('designer');
        $normalizedAsset['values']->shouldBeEqualTo([
            'tags' => ['industrial', 'street furniture'],
        ]);
        $normalizedAsset['asset_full_text_search']->shouldBeEqualTo([
                'ecommerce' => [
                    'fr_FR' => "stark Bio",
                ],
                'mobile'    => [
                    'en_US' => "stark Bio",
                ],
            ]
        );
        $normalizedAsset['asset_code_label_search']->shouldBeLike([
                'fr_FR' => 'stark Philippe Stark',
                'en_US' => 'stark',
                'de_DE' => 'stark',
            ]
        );
        $normalizedAsset['complete_value_keys']->shouldBeEqualTo([
                'name'                     => true,
                'description_mobile_en_US' => true,
                'tags' => true,
            ]
        );
        $normalizedAsset['updated_at']->shouldBeInt();
    }

    function it_normalizes_a_searchable_assets_by_asset_identifiers(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindActivatedLocalesInterface $findActivatedLocales,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        \Iterator $searchableAssetItemIterator
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetIdentifiers = [
            AssetIdentifier::fromString('stark'),
            AssetIdentifier::fromString('coco')
        ];

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
            'tags' => [
                'data' => ['industrial', 'street furniture']
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
            'tags' => [
                'data' => ['fashion', 'fragrance']
            ],
        ];

        $findActivatedLocales->findAll()->shouldBeCalledOnce()->willReturn(['en_US', 'fr_FR', 'de_DE']);
        $findSearchableAssets
            ->byAssetIdentifiers($assetIdentifiers)
            ->willReturn($searchableAssetItemIterator);
        $searchableAssetItemIterator->valid()->willReturn(true, true, false);
        $searchableAssetItemIterator->rewind()->shouldBeCalled();
        $searchableAssetItemIterator->next()->shouldBeCalled();
        $searchableAssetItemIterator->current()->willReturn($stark, $coco);

        $findValueKeysToIndexForAllChannelsAndLocales->find($assetFamilyIdentifier)
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
                $assetFamilyIdentifier,
                ['option', 'option_collection']
            )
            ->willReturn(['tags']);

        $normalizedAssets = $this->normalizeAssets($assetFamilyIdentifier, $assetIdentifiers);
        $normalizedAssets[0]['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedAssets[0]['code']->shouldBeEqualTo('stark');
        $normalizedAssets[0]['asset_family_code']->shouldBeEqualTo('designer');
        $normalizedAssets[1]['identifier']->shouldBeEqualTo('designer_coco_fingerprint');
        $normalizedAssets[1]['code']->shouldBeEqualTo('coco');
        $normalizedAssets[1]['asset_family_code']->shouldBeEqualTo('designer');
    }
}
