<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessFamilyMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessFamilyMaskPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessFamilyMasks;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProducts;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator implements CompletenessCalculatorInterface
{
    /** @var GetProducts */
    private $getProducts;

    /** @var GetCompletenessFamilyMasks */
    private $getCompletenessFamilyMasks;

    public function __construct(
        GetProducts $getProducts,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $this->getProducts = $getProducts;
        $this->getCompletenessFamilyMasks = $getCompletenessFamilyMasks;
    }

    public function calculate(ProductInterface $product): array
    {
        throw new \Exception('Drop this from the interface!');
    }

    public function fromProductIdentifiers($productIdentifiers): array
    {
        $productMasks = $this->getProductMasks($productIdentifiers);

        $familyCodes = array_map(function (CompletenessProductMask $product) {
            return $product->familyCode();
        }, $productMasks);

        $familyMasks = $this->getFamilyMasks($familyCodes);

        $result = [];
        foreach ($productMasks as $productMask) {
            $familyMask = $familyMasks[$productMask->familyCode()];
            $result[$productMask->getId()] = $this->applyMask($familyMask, $productMask);
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
     * @return CompletenessProductMask[]
     */
    private function getProductMasks(array $productIdentifiers): array
    {
        return $this->getProducts->fromProductIdentifiers($productIdentifiers);
    }

    /**
     * @param string[] $familyCodes
     *
     * @return CompletenessFamilyMask[]
     */
    private function getFamilyMasks(array $familyCodes): array
    {
        return $this->getCompletenessFamilyMasks->fromFamilyCodes($familyCodes);
    }

    private function applyMask(
        CompletenessFamilyMask $familyMask,
        CompletenessProductMask $completenessProductMask
    ): ProductCompletenessCollection {
        $productMask = $completenessProductMask->mask();

        return new ProductCompletenessCollection($completenessProductMask->getId(), array_map(
            function (CompletenessFamilyMaskPerChannelAndLocale $familyMaskPerChannelAndLocale) use ($productMask) {
                $diff = array_diff($familyMaskPerChannelAndLocale->mask(), $productMask);

                $missingAttributeCodes = array_map(function (string $mask) {
                    return substr($mask, 0, strpos($mask, '-'));
                }, $diff);

                return new ProductCompleteness(
                    $familyMaskPerChannelAndLocale->channelCode(),
                    $familyMaskPerChannelAndLocale->localeCode(),
                    count($familyMaskPerChannelAndLocale->mask()),
                    $missingAttributeCodes
                );
            },
            $familyMask->masks()
        ));
    }
}
