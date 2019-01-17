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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemoryIdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    /** @var IdentifiersMapping */
    private $identifiersMapping;

    public function __construct()
    {
        $this->identifiersMapping = new IdentifiersMapping([]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiersMapping = $identifiersMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): IdentifiersMapping
    {
        $newIdentifiersMapping = new IdentifiersMapping([]);
        foreach ($this->identifiersMapping as $identifier) {
            $newIdentifiersMapping->map($identifier->getFranklinCode(), $identifier->getAttribute());
        }

        return $newIdentifiersMapping;
    }
}
