<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByCodesInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use PhpSpec\ObjectBehavior;

class AssetValueHydratorSpec extends ObjectBehavior
{    public function let(FindAssetLabelsByCodesInterface $findAssetLabelsByCodes)
    {
        $this->beConstructedWith($findAssetLabelsByCodes);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(ValueHydratorInterface::class);
    }

    public function it_supports_asset_type_attributes(
        AssetAttribute $assetAttribute,
        AssetCollectionAttribute $assetCollectionAttribute,
        AbstractAttribute $otherAttribute
    ) {
        $this->supports($assetAttribute)->shouldReturn(true);
        $this->supports($assetCollectionAttribute)->shouldReturn(true);
        $this->supports($otherAttribute)->shouldReturn(false);
    }

    public function it_fills_labels_linked_asset_collection_into_a_value_context(
        AssetAttribute $assetAttribute
    ) {
        $assetAttribute->getType()->willReturn('asset_collection');
        $normalizedValue = [
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => ['ikea_id', 'madecom_id'],
        ];

        $context = [
            'labels' => [
                'adidas_id' => ['en_US' => 'Adidas', 'fr_FR' => 'Adidas'],
                'nike_id' => ['en_US' => 'Nike', 'fr_FR' => 'Nike'],
                'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
                'madecom_id' => ['en_US' => 'Made.com', 'fr_FR' => 'Made.com'],
            ]
        ];

        $this->hydrate($normalizedValue, $assetAttribute, $context)->shouldReturn([
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => ['ikea_id', 'madecom_id'],
            'context' => [
                'labels' => [
                    'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
                    'madecom_id' => ['en_US' => 'Made.com', 'fr_FR' => 'Made.com'],
                ]
            ]
        ]);
    }

    public function it_fills_labels_linked_asset_into_a_value_context(
        AssetAttribute $assetAttribute
    ) {
        $assetAttribute->getType()->willReturn('asset');
        $normalizedValue = [
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => 'ikea_id',
        ];

        $context = [
            'labels' => [
                'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
            ]
        ];

        $this->hydrate($normalizedValue, $assetAttribute, $context)->shouldReturn([
           'attribute' => 'brands',
           'locale' => null,
           'channel' => null,
           'data' => 'ikea_id',
           'context' => [
               'labels' => [
                   'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
               ]
           ]
       ]);
    }

    public function it_erase_removed_linked_asset_from_the_data(AssetAttribute $assetAttribute)
    {
        $assetAttribute->getType()->willReturn('asset');
        $normalizedValue = [
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => 'ikea_id',
        ];

        $context = [
            'labels' => []
        ];

        $this->hydrate($normalizedValue, $assetAttribute, $context)->shouldReturn([
           'attribute' => 'brands',
           'locale' => null,
           'channel' => null,
           'data' => null,
           'context' => [
               'labels' => []
           ]
       ]);
    }
}
