<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\PQB;

use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductUuidCursor implements ProductUuidCursorInterface
{
    /**
     * @var UuidInterface[]|null
     */
    protected ?array $uuids = null;
    protected int $count;

    private function __construct(private ProductUuidQueryFetcher $fetcher)
    {
    }

    public static function createFromFetcher(ProductUuidQueryFetcher $fetcher): ProductUuidCursor
    {
        return new self($fetcher);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->uuids) {
            $this->rewind();
        }

        return \current($this->uuids ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->uuids) {
            $this->rewind();
        }

        return \key($this->uuids ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->uuids) {
            $this->rewind();
        }

        return !empty($this->uuids);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->uuids) {
            $this->rewind();
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (null === $this->uuids || false === next($this->uuids)) {
            $productResults = $this->fetcher->getNextResults();
            $this->uuids = $productResults->uuids();
            $this->count = $productResults->count();
            \reset($this->uuids);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->fetcher->reset();
        $productResults = $this->fetcher->getNextResults();
        $this->uuids = $productResults->uuids();
        $this->count = $productResults->count();
        \reset($this->uuids);
    }
}
