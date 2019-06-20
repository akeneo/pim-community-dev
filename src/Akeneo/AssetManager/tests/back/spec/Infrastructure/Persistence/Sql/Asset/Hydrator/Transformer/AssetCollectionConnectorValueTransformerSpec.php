<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionConnectorValueTransformerSpec extends ObjectBehavior
{
    function let(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->beConstructedWith($findCodesByIdentifiers);
    }

    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_asset_attribute(
        TextAttribute $textAttribute,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $this->supports($assetCollectionAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(
        $findCodesByIdentifiers,
        AssetCollectionAttribute $attribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $attribute->getAssetType()->willReturn($assetFamilyIdentifier);
        $findCodesByIdentifiers
            ->find(['kartell', 'lexon', 'cogip'])
            ->willReturn([
                'cogip_79505e53-9694-47e1-aa5d-d8812c5ed699' => 'cogip',
                'kartell_00fb9223-8636-4707-aa43-9058acfdfbe4' => 'kartell',
                'lexon__076948af-6b73-4844-80f3-1a033998874b' => 'lexon',
            ]);

        $this->transform([
            'data'      => ['kartell', 'lexon', 'cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['cogip', 'kartell', 'lexon'],
        ]);
    }

    function it_removes_assets_that_do_not_exist_in_a_value_containing_assets(
        $findCodesByIdentifiers,
        AssetCollectionAttribute $attribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $attribute->getAssetType()->willReturn($assetFamilyIdentifier);
        $findCodesByIdentifiers
            ->find(['kartell', 'lexon', 'cogip'])
            ->willReturn(['lexon']);

        $this->transform([
            'data'      => ['kartell', 'lexon', 'cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['lexon'],
        ]);
    }

    function it_returns_null_if_no_assets_exist(
        $findCodesByIdentifiers,
        AssetCollectionAttribute $attribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $attribute->getAssetType()->willReturn($assetFamilyIdentifier);
        $findCodesByIdentifiers->find(['cogip'])->willReturn([]);

        $this->transform([
            'data'      => ['cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn(null);
    }
}
