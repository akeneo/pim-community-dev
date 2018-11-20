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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemoryIdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    /** @var IdentifiersMapping */
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
    public function find(): IdentifiersMapping
    {
        return $this->identifiers;
    }
}
