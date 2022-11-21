<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessCollection implements \IteratorAggregate, \Countable
{
    /** @var ProductCompleteness[] */
    private array $completenesses = [];

    public function __construct(private UuidInterface $productUuid, array $completenesses)
    {
        foreach ($completenesses as $completeness) {
            $this->add($completeness);
        }
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->completenesses);
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

    public function count(): int
    {
        return count($this->completenesses);
    }
}
