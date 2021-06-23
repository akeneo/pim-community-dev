<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

class AssetCollectionSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported(
        FindAssetLabelTranslation $findAssetLabelTranslation
    ) {
        $this->beConstructedWith(['pim_catalog_asset_collection'], $findAssetLabelTranslation);

        $assetCollectionAttribute = $this->createAssetCollectionAttribute('ass_col_attribute', 'packshot');
        $this->supports(['type' => 'code'], $assetCollectionAttribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $assetCollectionAttribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $assetCollectionAttribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        FindAssetLabelTranslation $findAssetLabelTranslation,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['pim_catalog_asset_collection'], $findAssetLabelTranslation);

        $assetCollectionAttribute = $this->createAssetCollectionAttribute('ass_col_attribute', 'packshot');
        $value->getData()->willReturn(['admete_1', 'admete_2', 'absorb_1']);

        $this->applySelection(['type' => 'code', 'separator' => ','], $entity, $assetCollectionAttribute, $value)
            ->shouldReturn('admete_1,admete_2,absorb_1');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        FindAssetLabelTranslation $findAssetLabelTranslation,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['pim_catalog_asset_collection'], $findAssetLabelTranslation);

        $assetCollectionAttribute = $this->createAssetCollectionAttribute('ass_col_attribute', 'packshot');
        $value->getData()->willReturn(['admete_1', 'admete_2', 'absorb_1']);

        $findAssetLabelTranslation->byFamilyCodeAndAssetCodes('packshot', ['admete_1', 'admete_2', 'absorb_1'], 'fr_FR')
            ->willReturn([
                'admete_1' => 'Admete 1',
                'admete_2' => 'Admete 2',
                'absorb_1' => 'Absorb 1',
            ]);

        $this->applySelection(
            ['type' => 'label', 'locale' => 'fr_FR', 'separator' => ';'],
            $entity,
            $assetCollectionAttribute,
            $value
        )->shouldReturn('Admete 1;Admete 2;Absorb 1');
    }

    public function it_selects_the_code_when_label_is_undefined(
        ValueInterface $value,
        FindAssetLabelTranslation $findAssetLabelTranslation,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['pim_catalog_asset_collection'], $findAssetLabelTranslation);

        $assetCollectionAttribute = $this->createAssetCollectionAttribute('ass_col_attribute', 'packshot');
        $value->getData()->willReturn(['admete_1', 'admete_2', 'absorb_1']);

        $findAssetLabelTranslation->byFamilyCodeAndAssetCodes('packshot', ['admete_1', 'admete_2', 'absorb_1'], 'fr_FR')
            ->willReturn([
                'admete_1' => 'Admete 1',
                'admete_2' => null,
                'absorb_1' => 'Absorb 1',
            ]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR', 'separator' => '|'], $entity, $assetCollectionAttribute, $value)
            ->shouldReturn('Admete 1|[admete_2]|Absorb 1');
    }

    private function createAssetCollectionAttribute(string $name, string $assetFamilyCode): Attribute
    {
        return new Attribute(
            $name,
            'pim_catalog_asset_collection',
            ['reference_data_name' => $assetFamilyCode],
            false,
            false,
            null,
            null,
            null,
            'pim_catalog_asset_collection',
            []
        );
    }
}
