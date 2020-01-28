<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class PublishedProductCompletenessCollection implements \IteratorAggregate
{
    /** @var int */
    private $publishedProductId;

    /** @var PublishedProductCompleteness[] */
    private $completenesses = [];

    public function __construct(int $publishedProductId, array $completenesses)
    {
        $this->publishedProductId = $publishedProductId;
        foreach ($completenesses as $completeness) {
            $this->add($completeness);
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->completenesses);
    }

    public function publishedProductId(): int
    {
        return $this->publishedProductId;
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->completenesses);
    }

    public function getCompletenessForChannelAndLocale(
        string $channelCode,
        string $localeCode
    ): ?PublishedProductCompleteness {
        $key = sprintf('%s-%s', $channelCode, $localeCode);

        return $this->completenesses[$key] ?? null;
    }

    private function add(PublishedProductCompleteness $completeness): void
    {
        $key = sprintf('%s-%s', $completeness->channelCode(), $completeness->localeCode());
        $this->completenesses[$key] = $completeness;
    }
}
