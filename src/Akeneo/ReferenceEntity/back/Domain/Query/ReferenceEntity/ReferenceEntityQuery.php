<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source filters.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Object representing a reference entity query
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityQuery
{
    private function __construct(
        private int $size,
        private ?ReferenceEntityIdentifier $searchAfterIdentifier
    ) {
    }

    public static function createPaginatedQuery(
        int $size,
        ?ReferenceEntityIdentifier $searchAfterIdentifier
    ): ReferenceEntityQuery {
        return new ReferenceEntityQuery(
            $size,
            $searchAfterIdentifier
        );
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getSearchAfterIdentifier(): ?string
    {
        return null !== $this->searchAfterIdentifier ? (string) $this->searchAfterIdentifier : null;
    }
}
