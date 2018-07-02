<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Application;

use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMapping;
use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMappingHandler;
use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

class ManageMapping
{
    private $updateIdentifiersMappingHandler;

    private $identifiersMappingRepository;

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
