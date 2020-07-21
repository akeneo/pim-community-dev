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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

abstract class AbstractGetProductIdsByAttributeOptionCodeQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function givenALocalizableMultiSelectAttributeWithOptions(): void
    {
        $attributeCode = 'a_localizable_multi_select';
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::OPTION_MULTI_SELECT,
            'group' => 'other',
            'localizable' => true,
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->createAttributeOption($attributeCode, 'optionA');
        $this->createAttributeOption($attributeCode, 'optionB');

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $this->get('pim_catalog.updater.family')->update($family, [
            'attributes' => array_merge($family->getAttributeCodes(), [$attributeCode])
        ]);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function createAttributeOption(string $attributeCode, string $optionCode): AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => [
                'en_US' => sprintf('%s US', $optionCode),
                'fr_FR' => sprintf('%s FR', $optionCode),
            ],
        ]);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }

    protected function createProduct(array $values): ProductId
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct(strval(Uuid::uuid4()), 'family_A');

        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    protected function createProductVariant(string $parentCode): ProductId
    {
        $productVariant = $this->get('pim_catalog.builder.product')->createProduct(strval(Uuid::uuid4()), 'family_A');
        $this->get('pim_catalog.updater.product')->update($productVariant, ['parent' => $parentCode]);
        $this->get('pim_catalog.saver.product')->save($productVariant);

        return new ProductId($productVariant->getId());
    }

    protected function createProductModel(string $code, array $values, ?string $parent = null): ProductId
    {
        $productModelBuilder = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant('familyVariantA1');

        if (null !== $parent) {
            $productModelBuilder->withParent($parent);
        }

        $productModel = $productModelBuilder->build();

        if (!empty($values)) {
            $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => $values]);
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }
}
