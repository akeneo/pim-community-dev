<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * This class does the mapping of every way to identify a product.
 * A product can be identified by
 * - its uuid (always present)
 * - its id (always present for now)
 * - its identifier (the value through the identifier attribute, not mandatory, can be null).
 *
 * Each method allows the developer to retrieve a product identifier (uuid, id or identifier) from another product
 * identifier.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UuidMapping
{
    /** @var array<string, string> */
    private array $uuidsToIdentifiers = [];

    /** @var array<string, string> */
    private array $identifiersToUuids = [];

    /** @var array<string, int> */
    private array $identifiersToIds = [];

    /** @var array<string, int> */
    private array $uuidsToIds = [];

    /** @var array<int, string> */
    private array $idsToIdentifiers = [];

    /** @var array<int, string> */
    private array $idsToUuids = [];

    private function __construct(array $mapping)
    {
        foreach ($mapping as $line) {
            Assert::keyExists($line, 'uuid');
            Assert::keyExists($line, 'identifier');
            Assert::keyExists($line, 'id');
            Assert::stringNotEmpty($line['uuid']);
            Assert::nullOrStringNotEmpty($line['identifier']);
            Assert::numeric($line['id']);
            Assert::notNull($line['id']);
            Assert::uuid($line['uuid'], sprintf('Invalid uuid "%s"', $line['uuid']));

            $this->uuidsToIdentifiers[$line['uuid']] = $line['identifier'];
            $this->uuidsToIds[$line['uuid']] = $line['id'];
            $this->idsToIdentifiers[$line['id']] = $line['identifier'];
            $this->idsToUuids[$line['id']] = $line['uuid'];

            if (null !== $line['identifier']) {
                $this->identifiersToUuids[$line['identifier']] = Uuid::fromString($line['uuid']);
                $this->identifiersToIds[$line['identifier']] = $line['id'];
            }
        }
    }

    public static function createFromMapping(array $mapping): self
    {
        return new self($mapping);
    }

    public function getUuidFromIdentifier(string $identifier): UuidInterface
    {
        Assert::keyExists($this->identifiersToUuids, $identifier);

        return $this->identifiersToUuids[$identifier];
    }

    public function hasUuid(string $identifier): bool
    {
        return isset($this->identifiersToUuids[$identifier]);
    }

    public function getIdentifier(UuidInterface $uuid): string
    {
        Assert::keyExists($this->uuidsToIdentifiers, $uuid->toString());

        return $this->uuidsToIdentifiers[$uuid->toString()];
    }

    public function hasIdentifier(UuidInterface $uuid): bool
    {
        return isset($this->uuidsToIdentifiers[$uuid->toString()]);
    }

    public function getIdFromIdentifier(string $identifier): ?int
    {
        return $this->identifiersToIds[$identifier] ?? null;
    }

    public function getIdFromUuid(string $uuid): ?int
    {
        return $this->uuidsToIds[$uuid] ?? null;
    }

    public function getUuidFromId(int $id): ?string
    {
        return $this->idsToUuids[$id] ?? null;
    }

    public function hasIdentifierFromId(int $id): bool
    {
        return isset($this->idsToIdentifiers[$id]);
    }

    public function getIdentifierFromId(int $id): ?string
    {
        return $this->idsToIdentifiers[$id] ?? null;
    }

    public function hasUuidFromId(int $id): bool
    {
        return isset($this->idsToUuids[$id]);
    }
}
