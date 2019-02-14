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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\IdentifiersMappingNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingController
{
    use CheckAccessTrait;

    /** @var GetIdentifiersMappingHandler */
    private $getIdentifiersMappingHandler;

    /** @var SaveIdentifiersMappingHandler */
    private $saveIdentifiersMappingHandler;

    /**
     * @param GetIdentifiersMappingHandler $getIdentifiersMappingHandler
     * @param SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler,
        SecurityFacade $securityFacade
    ) {
        $this->getIdentifiersMappingHandler = $getIdentifiersMappingHandler;
        $this->saveIdentifiersMappingHandler = $saveIdentifiersMappingHandler;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveIdentifiersMappingAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $this->checkAccess('akeneo_franklin_insights_settings_mapping');

        $identifiersMapping = json_decode($request->getContent(), true);

        try {
            $command = new SaveIdentifiersMappingCommand($identifiersMapping);
            $this->saveIdentifiersMappingHandler->handle($command);

            return new JsonResponse(json_encode($identifiersMapping));
        } catch (InvalidMappingException $exception) {
            return new JsonResponse(
                [
                    [
                        'message' => $exception->getMessage(),
                        'messageParams' => $exception->getMessageParams(),
                        'path' => $exception->getPath(),
                        'global' => false,
                    ],
                ],
                $exception->getCode()
            );
        } catch (DataProviderException $exception) {
            return new JsonResponse(['errors' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction(): JsonResponse
    {
        $this->checkAccess('akeneo_franklin_insights_settings_mapping');

        $identifiersMappingNormalizer = new IdentifiersMappingNormalizer();
        $identifiersMapping = $this->getIdentifiersMappingHandler->handle(new GetIdentifiersMappingQuery());

        return new JsonResponse(
            $identifiersMappingNormalizer->normalize($identifiersMapping)
        );
    }
}
