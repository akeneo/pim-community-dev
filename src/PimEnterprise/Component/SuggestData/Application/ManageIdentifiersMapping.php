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

namespace PimEnterprise\Component\SuggestData\Application;

use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMapping;
use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMappingHandler;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

/**
 * Service to manage identifiers mapping
 */
class ManageIdentifiersMapping
{
    private $updateIdentifiersMappingHandler;
    private $identifiersMappingRepository;

    /**
     * @param UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param array $identifiers
     */
    public function updateIdentifierMapping(array $identifiers): void
    {
        $updateIdentifierCommand = new UpdateIdentifiersMapping($identifiers);
        $this->updateIdentifiersMappingHandler->handle($updateIdentifierCommand);
    }

    public function getIdentifiersMapping(): array
    {
        $identifiersMapping = $this->identifiersMappingRepository->findAll();

        return $identifiersMapping->normalize();
    }
}
