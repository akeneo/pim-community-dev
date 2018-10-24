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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Subscription;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DoesPersistedProductHaveFamilyIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_that_a_product_has_a_family_in_database(): void
    {
        $product = $this->createProductWithFamily();

        $productHasFamily = $this->get(
            'akeneo.pim.automation.suggest_data.service.product_subscription.does_persisted_product_have_a_family'
        )->check($product);

        Assert::true($productHasFamily);
    }

    public function test_that_a_product_has_no_family_in_database(): void
    {
        $product = $this->createProductWithoutFamily();

        $productHasFamily = $this->get(
            'akeneo.pim.automation.suggest_data.service.product_subscription.does_persisted_product_have_a_family'
        )->check($product);

        Assert::false($productHasFamily);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return ProductInterface
     */
    private function createProductWithFamily(): ProductInterface
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build(['code' => 'a_test_family']);

        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);

        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->withFamily($family->getCode())
            ->build();

        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @return ProductInterface
     */
    private function createProductWithoutFamily(): ProductInterface
    {
        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->build();

        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
