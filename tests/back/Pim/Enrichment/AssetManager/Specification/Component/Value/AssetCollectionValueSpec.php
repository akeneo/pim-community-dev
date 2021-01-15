<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionValueSpec extends ObjectBehavior
{
    private const ATTRIBUTE_CODE = 'assets';

    function let()
    {
        $this->beConstructedWith(
            self::ATTRIBUTE_CODE,
            [AssetCode::fromString('paint'), AssetCode::fromString('image')],
            'ecommerce',
            'fr_FR'
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(AssetCollectionValue::class);
    }

    function it_returns_data()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('image')];
        $this->beConstructedThrough(
            'value',
            [self::ATTRIBUTE_CODE, $data, null, null]
        );
        $this->getData()->shouldReturn($data);
    }

    function it_compares_simple_asset_collection_values()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('image')];
        $otherData = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];

        $this->beConstructedThrough(
            'value',
            [self::ATTRIBUTE_CODE, $data, null, null]
        );
        $sameAssetCollection = AssetCollectionValue::value(self::ATTRIBUTE_CODE, $data);
        $otherAssetCollection = AssetCollectionValue::value(self::ATTRIBUTE_CODE, $otherData);
        $scopableAssetCollection = AssetCollectionValue::scopableValue(self::ATTRIBUTE_CODE, $data, 'ecommerce');
        $localizableAssetCollection = AssetCollectionValue::localizableValue(self::ATTRIBUTE_CODE, $data, 'fr_FR');
        $scopableLocalizableAssetCollection = AssetCollectionValue::scopableLocalizableValue(self::ATTRIBUTE_CODE, $data, 'ecommerce', 'fr_FR');

        $this->isEqual($sameAssetCollection)->shouldReturn(true);
        $this->isEqual($otherAssetCollection)->shouldReturn(false);
        $this->isEqual($scopableAssetCollection)->shouldReturn(false);
        $this->isEqual($localizableAssetCollection)->shouldReturn(false);
        $this->isEqual($scopableLocalizableAssetCollection)->shouldReturn(false);
    }

    function it_compares_localizable_asset_collection_values()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('image')];
        $otherData = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];

        $this->beConstructedThrough(
            'localizableValue',
            [self::ATTRIBUTE_CODE, $data, 'fr_FR']
        );
        $sameAssetCollection = AssetCollectionValue::localizableValue(self::ATTRIBUTE_CODE, $data, 'fr_FR');
        $otherAssetCollection = AssetCollectionValue::localizableValue(self::ATTRIBUTE_CODE, $otherData, 'en_US');

        $this->isEqual($sameAssetCollection)->shouldReturn(true);
        $this->isEqual($otherAssetCollection)->shouldReturn(false);
    }

    function it_compares_scopable_asset_collection_values()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('image')];
        $otherData = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];

        $this->beConstructedThrough(
            'scopableValue',
            [self::ATTRIBUTE_CODE, $data, 'ecommerce']
        );
        $sameAssetCollection = AssetCollectionValue::scopableValue(self::ATTRIBUTE_CODE, $data, 'ecommerce');
        $otherAssetCollection = AssetCollectionValue::scopableValue(self::ATTRIBUTE_CODE, $otherData, 'ecommerce');

        $this->isEqual($sameAssetCollection)->shouldReturn(true);
        $this->isEqual($otherAssetCollection)->shouldReturn(false);
    }

    function it_compares_scopable_and_localizable_asset_collection_values()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('image')];
        $otherData = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];

        $this->beConstructedThrough(
            'scopableLocalizableValue',
            [self::ATTRIBUTE_CODE, $data, 'ecommerce', 'fr_FR']
        );
        $sameAssetCollection = AssetCollectionValue::scopableLocalizableValue(self::ATTRIBUTE_CODE, $data, 'ecommerce','fr_FR');
        $otherAssetCollection = AssetCollectionValue::scopableLocalizableValue(self::ATTRIBUTE_CODE, $otherData, 'print', 'en_US');

        $this->isEqual($sameAssetCollection)->shouldReturn(true);
        $this->isEqual($otherAssetCollection)->shouldReturn(false);
    }

    function it_compares_asset_code_order()
    {
        $data = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];
        $sameDataOrder = [AssetCode::fromString('paint'), AssetCode::fromString('bike')];
        $otherOrderData = [AssetCode::fromString('bike'), AssetCode::fromString('paint')];

        $this->beConstructedThrough(
            'scopableLocalizableValue',
            [self::ATTRIBUTE_CODE, $data, 'ecommerce', 'fr_FR']
        );

        $sameDataOrderAssetCollection = AssetCollectionValue::scopableLocalizableValue(
            self::ATTRIBUTE_CODE,
            $sameDataOrder,
            'ecommerce',
            'fr_FR'
        );

        $otherOrderAssetCollection = AssetCollectionValue::scopableLocalizableValue(
            self::ATTRIBUTE_CODE,
            $otherOrderData,
            'ecommerce',
            'fr_FR'
        );

        $this->isEqual($sameDataOrderAssetCollection)->shouldReturn(true);
        $this->isEqual($otherOrderAssetCollection)->shouldReturn(false);
    }
}
