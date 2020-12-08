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

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProduct;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class AddDefaultValuesIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_add_default_values_when_duplicating_a_product()
    {
        $command = new DuplicateProduct('original', 'duplicated', -1);
        $this->get('pimee_enrich.product.duplicate_product_handler')->handle($command);

        $duplicatedProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('duplicated');
        Assert::assertInstanceOf(ProductInterface::class, $duplicatedProduct);

        Assert::assertInstanceOf(ValueInterface::class, $duplicatedProduct->getValue('a_text'));
        Assert::assertNull($duplicatedProduct->getValue('a_yes_no_with_default_value'));
    }

    /**
     * @test
     */
    public function it_does_not_add_default_values_when_publishing_a_product()
    {
        $originalProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('original');
        $publishedProduct = $this->get('pimee_workflow.manager.published_product')->publish($originalProduct);

        Assert::assertInstanceOf(ValueInterface::class, $publishedProduct->getValue('a_text'));
        Assert::assertNull($publishedProduct->getValue('a_yes_no_with_default_value'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();

    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadFixtures(): void
    {
        // create original product
        $product = $this->get('pim_catalog.builder.product')->createProduct('original');
        $this->get('pim_catalog.updater.product')->update($product, [
            'identifier' => 'original',
            'family' => 'familyA',
            'values' => [
                'a_text' => [['data' => 'Lorem ipsum', 'locale' => null, 'scope' => null]],
            ],
        ]);
        Assert::assertCount(0, $this->get('pim_catalog.validator.product')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        // create an attribute with default value
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'a_yes_no_with_default_value',
            'type' => 'pim_catalog_boolean',
            'scopable' => false,
            'localizable' => false,
            'group' => 'attributeGroupA',
            'default_value' => false,
        ]);
        Assert::assertCount(0, $this->get('validator')->validate($attribute));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $familyA = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $familyA->addAttribute($attribute);
        $this->get('pim_catalog.saver.family')->save($familyA);
    }
}
