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

namespace AkeneoEnterprise\Test\Acceptance\SuggestData\Repository;

use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

class InMemoryIdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    private $identifiers;

    /**
     * @param IdentifiersMapping $identifiers
     */
    public function __construct(IdentifiersMapping $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiers = $identifiersMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): IdentifiersMapping
    {
        return $this->identifiers;
    }
}
