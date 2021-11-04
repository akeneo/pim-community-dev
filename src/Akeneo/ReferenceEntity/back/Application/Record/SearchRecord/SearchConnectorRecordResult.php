<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\SearchRecord;

use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Webmozart\Assert\Assert;

final class SearchConnectorRecordResult
{
    /** @var ConnectorRecord[] */
    private array $records;
    private ?string $lastSortValue;

    /**
     * @param ConnectorRecord[] $records
     */
    private function __construct(array $records, ?string $lastSortValue)
    {
        $this->records = $records;
        $this->lastSortValue = $lastSortValue;
    }

    public static function createFromSearchAfterQuery(array $records, ?string $lastSortValue)
    {
        Assert::allIsInstanceOf($records, ConnectorRecord::class);

        return new self($records, $lastSortValue);
    }

    /**
     * @return ConnectorRecord[]
     */
    public function records(): array
    {
        return $this->records;
    }

    public function lastSortValue(): ?string
    {
        return $this->lastSortValue;
    }
}
