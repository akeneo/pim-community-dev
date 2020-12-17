<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\FlatToStandard\EntityWithAssetValues;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;

class EntityWithAssetValuesSpec extends ObjectBehavior
{
    function let(ArrayConverterInterface $decoratedConverter, ObjectRepository $attributeRepository)
    {
        $attributeRepository->findBy(['type' => AssetCollectionType::ASSET_COLLECTION])->willReturn([
            $this->buildAttribute('asset', false, false),
            $this->buildAttribute('localizable_asset', true, false),
            $this->buildAttribute('scopable_asset', false, true),
            $this->buildAttribute('localizable_scopable_asset', true, true),
        ]);

        $this->beConstructedWith($decoratedConverter, $attributeRepository);
    }

    function it_can_be_instantiated()
    {
        $this->beAnInstanceOf(EntityWithAssetValues::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_removes_file_path_column_for_non_localizable_and_non_scopable_asset_attribute(
        ArrayConverterInterface $decoratedConverter
    ) {
        $item = [
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'asset' => 'asset1',
            'asset-file_path' => 'files/path/file.jpg',
        ];
        $decoratedConverter->convert([
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'asset' => 'asset1',
        ], [])->willReturn(['the' => 'result']);

        $this->convert($item, [])->shouldBe(['the' => 'result']);
    }

    function it_removes_file_path_columns_for_localizable_asset_attribute(
        ArrayConverterInterface $decoratedConverter
    ) {
        $item = [
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'localizable_asset-en_US' => 'asset1',
            'localizable_asset-en_US-file_path' => 'files/path/file.jpg',
            'localizable_asset-fr_FR' => 'asset2',
            'localizable_asset-fr_FR-file_path' => 'files/path/file.jpg',
        ];
        $decoratedConverter->convert([
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'localizable_asset-en_US' => 'asset1',
            'localizable_asset-fr_FR' => 'asset2',
        ], [])->willReturn(['the' => 'result']);

        $this->convert($item, [])->shouldBe(['the' => 'result']);
    }

    function it_removes_file_path_columns_for_scopable_asset_attribute(
        ArrayConverterInterface $decoratedConverter
    ) {
        $item = [
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'scopable_asset-mobile' => 'asset1',
            'scopable_asset-mobile-file_path' => 'files/path/file.jpg',
            'scopable_asset-tablet' => 'asset2',
            'scopable_asset-tablet-file_path' => 'files/path/file.jpg',
        ];
        $decoratedConverter->convert([
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'scopable_asset-mobile' => 'asset1',
            'scopable_asset-tablet' => 'asset2',
        ], [])->willReturn(['the' => 'result']);

        $this->convert($item, [])->shouldBe(['the' => 'result']);
    }

    function it_removes_file_path_columns_for_localizable_and_scopable_asset_attribute(
        ArrayConverterInterface $decoratedConverter
    ) {
        $item = [
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'localizable_scopable_asset-en_US-mobile' => 'asset1',
            'localizable_scopable_asset-en_US-mobile-file_path' => 'files/path/file.jpg',
            'localizable_scopable_asset-fr_FR-mobile' => 'asset2',
            'localizable_scopable_asset-fr_FR-mobile-file_path' => 'files/path/file.jpg',
            'localizable_scopable_asset-en_US-tablet' => 'asset3',
            'localizable_scopable_asset-en_US-tablet-file_path' => 'files/path/file.jpg',
            'localizable_scopable_asset-fr_FR-tablet' => 'asset4',
            'localizable_scopable_asset-fr_FR-tablet-file_path' => 'files/path/file.jpg',
        ];
        $decoratedConverter->convert([
            'sku' => 'the_sku',
            'name-fr_FR' => 'T-shirt super beau',
            'localizable_scopable_asset-en_US-mobile' => 'asset1',
            'localizable_scopable_asset-fr_FR-mobile' => 'asset2',
            'localizable_scopable_asset-en_US-tablet' => 'asset3',
            'localizable_scopable_asset-fr_FR-tablet' => 'asset4',
        ], [])->willReturn(['the' => 'result']);

        $this->convert($item, [])->shouldBe(['the' => 'result']);
    }

    private function buildAttribute(string $code, bool $isLocalizable, bool $isScopable): Attribute
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        $attribute->setLocalizable($isLocalizable);
        $attribute->setScopable($isScopable);

        return $attribute;
    }
}
