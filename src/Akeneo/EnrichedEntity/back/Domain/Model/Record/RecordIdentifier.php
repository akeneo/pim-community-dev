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
    private const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';
    private const IDENTIFIER = 'identifier';

    /** @var string */
    private $enrichedEntityIdentifier;

    /** @var string */
    private $identifier;

    private function __construct(string $enrichedEntityIdentifier, string $identifier)
    {
        Assert::stringNotEmpty($enrichedEntityIdentifier, 'Enriched entity identifier cannot be empty');
        Assert::maxLength(
            $enrichedEntityIdentifier,
            255,
            sprintf(
                'Enriched entity identifier cannot be longer than 255 characters, %d string long given',
                strlen($enrichedEntityIdentifier)
            )
        );
        Assert::regex(
            $enrichedEntityIdentifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf(
                'Enriched entity identifier may contain only letters, numbers and underscores, "%s" given',
                $enrichedEntityIdentifier
            )
        );

        Assert::stringNotEmpty($identifier, 'Record identifier cannot be empty');
        Assert::maxLength(
            $identifier,
            255,
            sprintf(
                'Record identifier cannot be longer than 255 characters, %d string long given',
                strlen($identifier)
            )
        );
        Assert::regex(
            $identifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf(
                'Record identifier may contain only letters, numbers and underscores, "%s" given',
                $identifier
            )
        );

        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->identifier = $identifier;
    }

    public static function create(string $enrichedEntityIdentifier, string $identifier): self
    {
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
