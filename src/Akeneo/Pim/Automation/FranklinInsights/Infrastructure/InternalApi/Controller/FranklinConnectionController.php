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

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\ConnectionStatusNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FranklinConnectionController
{
    use CheckAccessTrait;

    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var GetConfigurationHandler */
    private $getConfigurationHandler;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var ConnectionStatusNormalizer */
    private $connectionStatusNormalizer;

    /**
     * @param ActivateConnectionHandler $activateConnectionHandler
     * @param GetConfigurationHandler $getConfigurationHandler
     * @param GetConnectionStatusHandler $getConnectionStatusHandler
     * @param ConnectionStatusNormalizer $connectionStatusNormalizer
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        GetConnectionStatusHandler $getConnectionStatusHandler,
        ConnectionStatusNormalizer $connectionStatusNormalizer,
        SecurityFacade $securityFacade
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getConfigurationHandler = $getConfigurationHandler;
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
        $this->connectionStatusNormalizer = $connectionStatusNormalizer;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @return Response
     */
    public function getAction(): Response
    {
        $this->checkAccess('akeneo_franklin_insights_connection');

        $configuration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
        $token = $configuration->getToken();

        return new JsonResponse(
            [
                'code' => 'franklin',
                'values' => ['token' => (null === $token) ? null : (string) $token],
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getStatusAction(Request $request): Response
    {
        $checkTokenValidity = $request->query->getBoolean('checkValidity', false);
        $connectionStatus = $this->getConnectionStatusHandler->handle(
            new GetConnectionStatusQuery($checkTokenValidity)
        );

        return new JsonResponse($this->connectionStatusNormalizer->normalize($connectionStatus));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $this->checkAccess('akeneo_franklin_insights_connection');

        // TODO: We should $request->get('token', '') instead decoding json and getting back value
        // TODO: Why do we put message here instead of handling response code?
        // TODO: success = 200, invalid argument = 401, conf exception = 422
        $configurationFields = json_decode($request->getContent(), true);

        try {
            $tokenString = $configurationFields['token'] ?? '';

            $token = new Token($tokenString);
            $command = new ActivateConnectionCommand($token);
            $this->activateConnectionHandler->handle($command);
        } catch (ConnectionConfigurationException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'message' => 'akeneo_franklin_insights.connection.flash.error',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'message' => 'akeneo_franklin_insights.connection.flash.success',
        ]);
    }
}
