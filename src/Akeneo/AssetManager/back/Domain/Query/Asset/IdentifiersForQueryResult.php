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

namespace Akeneo\AssetManager\Domain\Query\Asset;

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

    /** @var string[] */
    public array $identifiers;
    public int $matchesCount;
    public ?array $lastSortValue;

    public function __construct(array $identifiers, int $matchesCount, ?array $lastSortValue = null)
    {
        Assert::allString($identifiers);
        $this->identifiers = $identifiers;
        $this->matchesCount = $matchesCount;
        $this->lastSortValue = $lastSortValue;
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIERS   => $this->identifiers,
            self::MATCHES_COUNT => $this->matchesCount,
        ];
    }
}
