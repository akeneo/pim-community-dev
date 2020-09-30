<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelRepositoryIntegration extends TestCase
{
    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->familyVariantRepository = $this->get('pim_catalog.repository.family_variant');
        $this->productModelRepository = $this->get('pim_catalog.repository.product_model');
    }

    /** @test */
    public function it_returns_product_models_for_a_given_family_variant(): void
    {
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier('clothing_color_size');
        self::assertNotNull($familyVariant);

        $productModels = $this->productModelRepository->findProductModelsForFamilyVariant($familyVariant);
        self::assertNotEmpty($productModels);

        $productModel = $productModels[0];
        self::assertSame($familyVariant->getCode(), $productModel->getFamilyVariant()->getCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
