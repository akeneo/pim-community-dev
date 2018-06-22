<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Application;

use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMapping;
use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMappingHandler;

class ManageMapping
{
    private $updateIdentifiersMappingHandler;

    public function __construct(UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler)
    {
        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
    }

    /**
     * @param array $identifiers
     * @return bool
     */
    public function updateIdentifierMapping(array $identifiers): bool
    {
        try {
            $updateIdentifierCommand = new UpdateIdentifiersMapping($identifiers);
            $this->updateIdentifiersMappingHandler->handle($updateIdentifierCommand);

            return true;
        }
        catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
