<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * Maps id to identifiers and vice versa
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdMapping
{
    /** @var array  */
    private $idsToIdentifiers;

    /** @var array  */
    private $identifiersToIds;

    private function __construct(array $mapping)
    {
        foreach ($mapping as $id => $identifier) {
            Assert::integer($id);
            Assert::stringNotEmpty($identifier);
        }

        $this->idsToIdentifiers = $mapping;
        $this->identifiersToIds = array_flip($mapping);
    }

    public static function createFromMapping(array $mapping): self
    {
        return new self($mapping);
    }

    public function getId(string $identifier): int
    {
        Assert::keyExists($this->identifiersToIds, $identifier);

        return $this->identifiersToIds[$identifier];
    }

    public function hasId(string $identifier): bool
    {
        return isset($this->identifiersToIds[$identifier]);
    }

    public function getIdentifier(int $id): string
    {
        Assert::keyExists($this->idsToIdentifiers, $id);

        return $this->idsToIdentifiers[$id];
    }

    public function hasIdentifier(int $id): bool
    {
        return isset($this->idsToIdentifiers[$id]);
    }
}
