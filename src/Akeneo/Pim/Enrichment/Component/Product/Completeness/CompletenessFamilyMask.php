<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

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

    /**
     * Example:
     *  [
     *      "tablet": ["a_multi_select", "a_scopable_price"],
     *      "mobile": ["a_file", "a_number_integer"]
     *  ]
     *
     * @var array
     */
    private $masksByChannel;

    public function __construct(
        string $familyCode,
        array $masksByChannel
    ) {
        $this->familyCode = $familyCode;
        $this->masksByChannel = $masksByChannel;
    }

    /**
     * @param Product  $product
     * @param string[] $localeCodes
     * @return ProductCompletenessCollection
     */
    public function getCompletenessCollection(Product $product, array $localeCodes): ProductCompletenessCollection
    {
        $productMask = $product->getMask();

        $completenesses = [];
        foreach ($localeCodes as $localeCode) {
            foreach (array_keys($this->masksByChannel) as $channelCode) {
                $familyMasks = $this->getFamilyMasks($channelCode, $localeCode);

                $diff = array_diff($familyMasks, $productMask);
                $missingAttributeCodes = array_map(function (string $mask) {
                    return substr($mask, 0, strpos($mask, '-'));
                }, $diff);

                $completeness = new ProductCompleteness(
                    $channelCode,
                    $localeCode,
                    count($familyMasks),
                    $missingAttributeCodes
                );

                $completenesses[] = $completeness;
            }
        }

        return new ProductCompletenessCollection($product->getId(), $completenesses);
    }

    private function getFamilyMasks(string $channelCode, string $localeCode): array
    {
        var_dump('Family mask');
        $result = [];
        foreach ($this->masksByChannel[$channelCode] as $attributeCode) {
            $familyMask = sprintf('%s-%s-%s', $attributeCode, $channelCode, $localeCode);

            var_dump($familyMask);

            $result[] = $familyMask;
        }

        return $result;
    }
}
