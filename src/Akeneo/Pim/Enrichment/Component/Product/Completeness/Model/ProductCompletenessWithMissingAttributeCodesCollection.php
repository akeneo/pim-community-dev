<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessWithMissingAttributeCodesCollection implements \IteratorAggregate, \Countable
{
    /** @var ProductCompletenessWithMissingAttributeCodes[] */
    private array $completenesses = [];

    public function __construct(private string $productId, array $completenesses)
    {
        foreach ($completenesses as $completeness) {
            $this->add($completeness);
        }
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function getIterator(): \Traversable
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

    public function count(): int
    {
        return count($this->completenesses);
    }

    /**
     * @return ProductWasCompletedOnChannelLocale[]
     */
    public function buildProductWasCompletedOnChannelLocaleEvents(
        \DateTimeImmutable $completedAt,
        ?ProductCompletenessCollection $previousProductCompletenessCollection,
        ?string $authorId,
    ): array {
        $events = [];
        $productUuid = ProductUuid::fromString($this->productId);

        foreach ($this->completenesses as $newProductCompleteness) {
            $previousProductCompleteness = $previousProductCompletenessCollection?->getCompletenessForChannelAndLocale(
                $newProductCompleteness->channelCode(),
                $newProductCompleteness->localeCode()
            );
            $previousRatio = $previousProductCompleteness?->ratio();
            if (100 !== $previousRatio && 100 === $newProductCompleteness->ratio()) {
                $events[] = new ProductWasCompletedOnChannelLocale(
                    $productUuid,
                    $completedAt,
                    $newProductCompleteness->channelCode(),
                    $newProductCompleteness->localeCode(),
                    $authorId
                );
            }
        }

        return $events;
    }

    private function add(ProductCompletenessWithMissingAttributeCodes $completeness): void
    {
        $key = sprintf('%s-%s', $completeness->channelCode(), $completeness->localeCode());
        $this->completenesses[$key] = $completeness;
    }
}
