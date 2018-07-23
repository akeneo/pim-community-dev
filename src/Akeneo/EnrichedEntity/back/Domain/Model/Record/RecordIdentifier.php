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
    public const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';
    public const IDENTIFIER= 'identifier';

    /** @var string */
    private $enrichedEntityIdentifier;

    /** @var string */
    private $identifier;

    private function __construct(string $enrichedEntityIdentifier, string $identifier)
    {
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->identifier = $identifier;
    }

    public static function from(string $enrichedEntityIdentifier, string $identifier): self
    {
        Assert::stringNotEmpty($enrichedEntityIdentifier);
        Assert::stringNotEmpty($identifier);

        if (1 !== preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException('Enriched entity identifier may contain only letters, numbers and underscores');
        }
        if (1 !== preg_match('/^[a-zA-Z0-9_]+$/', $enrichedEntityIdentifier)) {
            throw new \InvalidArgumentException('Record identifier may contain only letters, numbers and underscores');
        }

        return new self($enrichedEntityIdentifier, $identifier);
    }

    public function equals(RecordIdentifier $identifier): bool
    {
        return $this->enrichedEntityIdentifier === $identifier->enrichedEntityIdentifier &&
            $this->identifier === $identifier->identifier;
    }

    public function normalize(): array
    {
        return [
            self::ENRICHED_ENTITY_IDENTIFIER => $this->enrichedEntityIdentifier,
            self::IDENTIFIER                 => $this->identifier
        ];
    }

    public function getEnrichedEntityIdentifier(): string
    {
        return $this->enrichedEntityIdentifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
