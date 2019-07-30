<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessFamilyMaskPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFamilyMask
{
    /** @var string */
    private $familyCode;

    /** @var CompletenessFamilyMaskPerChannelAndLocale[] */
    private $masks;

    public function __construct(
        string $familyCode,
        array $masksByChannel
    ) {
        $this->familyCode = $familyCode;
        $this->masks = $masksByChannel;
    }

    /**
     * @param Product  $product
     *
     * @return ProductCompletenessCollection
     */
    public function getCompletenessCollection(Product $product): ProductCompletenessCollection
    {
        $productMask = $product->getMask();

        return new ProductCompletenessCollection($product->getId(), array_map(
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
            $this->masks
        ));
    }
}
