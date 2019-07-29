<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator implements CompletenessCalculatorInterface
{
    /** @var SqlGetProducts */
    private $getProducts;

    /** @var SqlGetCompletenessFamilyMasks */
    private $getCompletenessFamilyMasks;

    public function __construct(SqlGetProducts $getProducts, SqlGetCompletenessFamilyMasks $getCompletenessFamilyMasks)
    {
        $this->getProducts = $getProducts;
        $this->getCompletenessFamilyMasks = $getCompletenessFamilyMasks;
    }

    public function calculate(ProductInterface $product): array
    {
        throw new \Exception('Drop this from the interface!');
    }

    public function fromProductIdentifiers($productIdentifiers): array
    {
        $products = $this->fetchProducts($productIdentifiers);
        $familyCodes = array_map(function (Product $product) {
            return $product->familyCode();
        }, $products);

        $familyMasks = $this->fetchFamilyMasks($familyCodes);

        $result = [];
        foreach ($products as $product) {
            $familyMask = $familyMasks[$product->familyCode()];
            $result[$product->getId()] = $familyMask->getCompletenessCollection($product);
        }

        return $result;
    }

    public function fromProductIdentifier($productIdentifier): ProductCompletenessCollection
    {
        return $this->fromProductIdentifiers([$productIdentifier])[$productIdentifier];
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return Product[]
     */
    private function fetchProducts(array $productIdentifiers): array
    {
        return $this->getProducts->fromProductIdentifiers($productIdentifiers);
    }

    /**
     * @param string[] $familyCodes
     *
     * @return CompletenessFamilyMask[]
     */
    private function fetchFamilyMasks(array $familyCodes): array
    {
        return $this->getCompletenessFamilyMasks->fromFamilyCodes($familyCodes);
    }
}
