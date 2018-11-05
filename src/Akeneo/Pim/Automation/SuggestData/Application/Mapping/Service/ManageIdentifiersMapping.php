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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service;

use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;

/**
 * TODO: Change this class name.
 * Service to manage identifiers mapping.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ManageIdentifiersMapping
{
    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @return array
     */
    public function getIdentifiersMapping(): array
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();

        return $identifiersMapping->normalize();
    }
}
