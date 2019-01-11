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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Query\Product;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Product\SelectProductFamilyIdQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectProductFamilyIdQueryIntegration extends TestCase
{
    /** @var int */
    private $familyId;

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->familyId = null;
        parent::tearDown();
    }

    public function test_that_a_product_has_a_family_in_database(): void
    {
        $product = $this->createProductWithFamily();

        $familyId = $this->getQueryService()->execute($product->getId());

        Assert::eq($this->familyId, $familyId);
    }

    public function test_that_a_product_has_no_family_in_database(): void
    {
        $product = $this->createProductWithoutFamily();

        $familyId = $this->getQueryService()->execute($product->getId());

        Assert::null($familyId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return SelectProductFamilyIdQuery
     */
    private function getQueryService(): SelectProductFamilyIdQuery
    {
        return $this->get(
            'akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.product.select_product_family_id_query'
        );
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
        $this->familyId = $family->getId();

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
            ->withIdentifier('product_without_family')
            ->build();

        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
