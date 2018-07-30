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

namespace Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;

/**
 * Service to manage identifiers mapping.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ManageIdentifiersMapping
{
    /** @var UpdateIdentifiersMappingHandler */
    private $updateIdentifiersMappingHandler;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param array $identifiers
     */
    public function updateIdentifierMapping(array $identifiers): void
    {
        $updateIdentifierCommand = new UpdateIdentifiersMappingCommand($identifiers);
        $this->updateIdentifiersMappingHandler->handle($updateIdentifierCommand);
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
