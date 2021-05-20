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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Object representing an asset family query
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyQuery
{
    private ?int $size = null;

    private ?AssetFamilyIdentifier $searchAfterIdentifier = null;

    private function __construct(
        int $size,
        ?AssetFamilyIdentifier $searchAfterIdentifier
    ) {
        $this->size = $size;
        $this->searchAfterIdentifier = $searchAfterIdentifier;
    }

    public static function createPaginatedQuery(
        int $size,
        ?AssetFamilyIdentifier $searchAfterIdentifier
    ): AssetFamilyQuery {
        return new AssetFamilyQuery(
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
