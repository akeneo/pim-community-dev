<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Projection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessCollection implements \IteratorAggregate
{
    /** @var int */
    private $productId;

    /** @var ProductCompleteness[] */
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

    public function getCompletenessForChannelAndLocale(string $channelCode, string $localeCode): ?ProductCompleteness
    {
        $key = sprintf('%s-%s', $channelCode, $localeCode);

        return $this->completenesses[$key] ?? null;
    }

    private function add(ProductCompleteness $completeness): void
    {
        $key = sprintf('%s-%s', $completeness->channelCode(), $completeness->localeCode());
        $this->completenesses[$key] = $completeness;
    }
}
