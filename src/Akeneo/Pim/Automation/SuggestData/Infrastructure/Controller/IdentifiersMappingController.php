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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\ManageIdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingController
{
    /** @var ManageIdentifiersMapping */
    private $manageIdentifiersMapping;

    /** @var UpdateIdentifiersMappingHandler */
    private $updateIdentifiersMappingHandler;

    /**
     * @param ManageIdentifiersMapping $manageIdentifiersMapping
     * @param UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
     */
    public function __construct(
        ManageIdentifiersMapping $manageIdentifiersMapping,
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
    ) {
        $this->manageIdentifiersMapping = $manageIdentifiersMapping;
        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateIdentifiersMappingAction(Request $request): JsonResponse
    {
        $identifiersMapping = json_decode($request->getContent(), true);

        try {
            $command = new UpdateIdentifiersMappingCommand($identifiersMapping);
            $this->updateIdentifiersMappingHandler->handle($command);

            return new JsonResponse(json_encode($identifiersMapping));
        } catch (InvalidMappingException $invalidMapping) {
            return new JsonResponse([
                    [
                        'message' => $invalidMapping->getMessage(),
                        'messageParams' => $invalidMapping->getMessageParams(),
                        'path' => $invalidMapping->getPath(),
                        'global' => false,
                    ],
                ],
                $invalidMapping->getCode()
            );
        }
    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction(): JsonResponse
    {
        return new JsonResponse(
            $this->manageIdentifiersMapping->getIdentifiersMapping()
        );
    }
}
