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
    /** @var int|null */
    private $size;

    /** @var ReferenceEntityIdentifier|null */
    private $searchAfterCode;

    private function __construct(
        int $size,
        ?ReferenceEntityIdentifier $searchAfterCode
    ) {
        $this->size = $size;
        $this->searchAfterCode  = $searchAfterCode;
    }

    public static function createPaginatedQuery(
        int $size,
        ?ReferenceEntityIdentifier $searchAfterCode
    ): ReferenceEntityQuery {

        return new ReferenceEntityQuery(
            $size,
            $searchAfterCode
        );
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getSearchAfterCode(): ?string
    {
        return null !== $this->searchAfterCode ? (string) $this->searchAfterCode : null;
    }

}
