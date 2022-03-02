<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Maps uuid to identifiers and vice versa
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UuidMapping
{
    /** @var array<string, string> */
    private array $uuidsToIdentifiers;

    /** @var array<string, string> */
    private array $identifiersToUuids;

    private function __construct(array $mapping)
    {
        foreach ($mapping as $uuid => $identifier) {
            Assert::string($uuid);
            Assert::true(Uuid::isValid($uuid), sprintf('Invalid uuid "%s"', $uuid));
            Assert::stringNotEmpty($identifier);
        }

        $this->uuidsToIdentifiers = $mapping;
        $this->identifiersToUuids = array_map(fn (string $uuidAsStr): UuidInterface => Uuid::fromString($uuidAsStr), array_flip($mapping));
    }

    public static function createFromMapping(array $mapping): self
    {
        return new self($mapping);
    }

    public function getUuid(string $identifier): UuidInterface
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
}
