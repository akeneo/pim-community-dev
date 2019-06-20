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

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class AssetConnectorValueTransformerSpec extends ObjectBehavior
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
        AssetAttribute $assetAttribute
    ) {
        $this->supports($assetAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(
        $findCodesByIdentifiers,
        AssetAttribute $attribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $attribute->getAssetType()->willReturn($assetFamilyIdentifier);
        $findCodesByIdentifiers
            ->find(['france'])
            ->willReturn(['france']);
        $this->transform([
            'data'      => 'france',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'country_designer_fingerprint',
        ], $attribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'france',
        ]);
    }

    function it_returns_null_if_the_asset_does_not_exists(
        $findCodesByIdentifiers,
        AssetAttribute $attribute,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $attribute->getAssetType()->willReturn($assetFamilyIdentifier);
        $findCodesByIdentifiers
            ->find(['foo'])
            ->willReturn([]);

        $this->transform([
            'data'      => 'foo',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'country_designer_fingerprint',
        ], $attribute)->shouldReturn(null);
    }
}
