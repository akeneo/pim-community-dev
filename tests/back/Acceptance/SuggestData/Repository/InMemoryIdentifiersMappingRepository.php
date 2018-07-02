<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\SuggestData\Repository;

use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

class InMemoryIdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    private
        $identifiers;

    public function __construct(IdentifiersMapping $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    /**
     * @inheritDoc
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiers = $identifiersMapping;
    }

    /**
     * @inheritDoc
     */
    public function findAll(): IdentifiersMapping
    {
        return $this->identifiers;
    }
}
