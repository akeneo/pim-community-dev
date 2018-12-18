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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetIdentifiersMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer\IdentifiersMappingNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingController
{
    /** @var GetIdentifiersMappingHandler */
    private $getIdentifiersMappingHandler;

    /** @var UpdateIdentifiersMappingHandler */
    private $updateIdentifiersMappingHandler;

    /**
     * @param GetIdentifiersMappingHandler $getIdentifiersMappingHandler
     * @param UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
     */
    public function __construct(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
    ) {
        $this->getIdentifiersMappingHandler = $getIdentifiersMappingHandler;
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
        } catch (InvalidMappingException | DataProviderException $exception) {
            return new JsonResponse(
                [
                    [
                        'message' => $exception->getMessage(),
                        'messageParams' => $exception->getMessageParams(),
                        'path' => null,
                        'global' => false,
                    ],
                ],
                $exception->getCode()
            );
        }
    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction(): JsonResponse
    {
        $identifiersMappingNormalizer = new IdentifiersMappingNormalizer();
        $identifiersMapping = $this->getIdentifiersMappingHandler->handle(new GetIdentifiersMappingQuery());

        return new JsonResponse(
            $identifiersMappingNormalizer->normalize($identifiersMapping->getIdentifiers())
        );
    }
}
