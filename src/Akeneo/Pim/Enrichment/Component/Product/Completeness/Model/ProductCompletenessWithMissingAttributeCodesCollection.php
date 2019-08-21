<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessWithMissingAttributeCodesCollection implements \IteratorAggregate, \Countable
{
    /** @var int */
    private $productId;

    /** @var ProductCompletenessWithMissingAttributeCodes[] */
    private $completenesses = [];

    public function __construct(int $productId, array $completenesses)
    {
        $this->productId = $productId;
        foreach ($completenesses as $completeness) {
            $this->add($completeness);
        }
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->completenesses);
    }

    public function isEmpty(): bool
    {
        return count($this->completenesses) === 0;
    }

    public function getCompletenessForChannelAndLocale(string $channelCode, string $localeCode): ?ProductCompletenessWithMissingAttributeCodes
    {
        $key = sprintf('%s-%s', $channelCode, $localeCode);

        return $this->completenesses[$key] ?? null;
    }

    public function count()
    {
        return count($this->completenesses);
    }

    private function add(ProductCompletenessWithMissingAttributeCodes $completeness): void
    {
        $key = sprintf('%s-%s', $completeness->channelCode(), $completeness->localeCode());
        $this->completenesses[$key] = $completeness;
    }
}
