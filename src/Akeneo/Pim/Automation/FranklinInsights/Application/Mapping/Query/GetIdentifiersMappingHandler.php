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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetIdentifiersMappingHandler
{
    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    public function handle(GetIdentifiersMappingQuery $query): IdentifiersMapping
    {
        return $this->identifiersMappingRepository->find();
    }
}
