<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * Mapp id to identifiers and vice versa
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdMapping
{
    private $idsToIdentifiers;
    private $identifiersToIds;

    private function __construct(array $mapping)
    {
        foreach ($mapping as $id => $identifier) {
            Assert::integer($id);
            Assert::isString($identifier);
        }

        $this->idsToIdentifiers = $mapping;
        $this->identifiersToIds = array_flip($mapping);
    }

    public static function createFromMapping(array $mapping)
    {
        return new IdMapping($mapping);
    }

    public function getId(string $identifier): int
    {
        Assert::keyExists($this->identifiersToIds, $identifier);

        return $this->identifiersToIds[$identifier];
    }

    public function getIdentifier(int $id): string
    {
        Assert::keyExists($this->idsToIdentifiers, $id);

        return $this->identifiersToIds[$id];
    }
}
