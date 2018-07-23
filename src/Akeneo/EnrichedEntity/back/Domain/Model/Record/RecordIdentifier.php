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

namespace Akeneo\EnrichedEntity\Domain\Model\Record;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIdentifier
{
    /** @var string */
    private $enrichedEntityIdentifier;

    /** @var string */
    private $recordIdentifier;

    private function __construct(string $enrichedEntityIdentifier, string $recordIdentifier)
    {
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->recordIdentifier = $recordIdentifier;
    }

    public static function fromString(string $enrichedEntityIdentifier, string $recordIdentifier): self
    {
        Assert::stringNotEmpty($enrichedEntityIdentifier);
        Assert::stringNotEmpty($recordIdentifier);

        if (1 !== preg_match('/^[a-zA-Z0-9_]+$/', $recordIdentifier)) {
            throw new \InvalidArgumentException('Enriched entity identifier may contain only letters, numbers and underscores');
        }
        if (1 !== preg_match('/^[a-zA-Z0-9_]+$/', $enrichedEntityIdentifier)) {
            throw new \InvalidArgumentException('Record identifier may contain only letters, numbers and underscores');
        }

        return new self($enrichedEntityIdentifier, $recordIdentifier);
    }

    public function equals(RecordIdentifier $identifier): bool
    {
        return $this->enrichedEntityIdentifier === $identifier->enrichedEntityIdentifier &&
            $this->recordIdentifier === $identifier->recordIdentifier;
    }

    public function __toString()
    {
        return sprintf('%s_%s', $this->enrichedEntityIdentifier, $this->recordIdentifier);
    }
}
