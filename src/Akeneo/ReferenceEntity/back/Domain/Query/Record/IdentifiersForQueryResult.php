<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Webmozart\Assert\Assert;

/**
 * Read model representing a search result
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IdentifiersForQueryResult
{
    private const IDENTIFIERS = 'identifiers';
    private const MATCHES_COUNT = 'matches_count';

    /** @param string[] $identifiers */
    public function __construct(
        public array $identifiers,
        public int $matchesCount,
        public ?string $lastSortValue
    ) {
        Assert::allString($identifiers);
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIERS => $this->identifiers,
            self::MATCHES_COUNT => $this->matchesCount,
        ];
    }
}
