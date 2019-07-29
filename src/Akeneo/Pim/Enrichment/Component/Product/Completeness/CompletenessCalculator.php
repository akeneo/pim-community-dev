<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

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
            // TODO Need the activated locales for this family. Not sure if it depends of the channel.
            $localeCodes = ['en_US'];
            $result[] = $familyMask->getCompletenessCollection($product, $localeCodes);
        }

        return $result;
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
