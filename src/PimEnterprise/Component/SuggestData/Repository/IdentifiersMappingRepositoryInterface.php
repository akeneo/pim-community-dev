<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Repository;

use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;

interface IdentifiersMappingRepositoryInterface
{
    /**
     * @param IdentifiersMapping $identifiersMapping
     */
    public function save(IdentifiersMapping $identifiersMapping): void;

    /**
     * @return IdentifiersMapping
     */
    public function findAll(): IdentifiersMapping;
}
