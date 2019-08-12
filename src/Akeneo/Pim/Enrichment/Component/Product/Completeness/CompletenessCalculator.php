<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessFamilyMasks;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CompletenessCalculator
{
    /** @var GetCompletenessProductMasks */
    private $getCompletenessProductMasks;

    /** @var GetCompletenessFamilyMasks */
    private $getCompletenessFamilyMasks;

    public function __construct(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $this->getCompletenessProductMasks = $getCompletenessProductMasks;
        $this->getCompletenessFamilyMasks = $getCompletenessFamilyMasks;
    }

    public function fromProductIdentifiers($productIdentifiers): array
    {
        $productMasks = $this->getCompletenessProductMasks->fromProductIdentifiers($productIdentifiers);

        $familyCodes = array_map(function (CompletenessProductMask $product) {
            return $product->familyCode();
        }, $productMasks);

        $familyMasks = $this->getCompletenessFamilyMasks->fromFamilyCodes(array_unique($familyCodes));

        $result = [];
        foreach ($productMasks as $productMask) {
            $familyMask = $familyMasks[$productMask->familyCode()];
            $result[$productMask->identifier()] = $familyMask->completenessCollectionForProduct($productMask);
        }

        return $result;
    }

    public function fromProductIdentifier($productIdentifier): ProductCompletenessWithMissingAttributeCodesCollection
    {
        return $this->fromProductIdentifiers([$productIdentifier])[$productIdentifier];
    }
}
