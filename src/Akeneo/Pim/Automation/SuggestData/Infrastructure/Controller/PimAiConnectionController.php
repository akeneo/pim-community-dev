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

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi\ConnectionStatusNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConnectionController
{
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
     * @param GetConnectionStatusHandler $getConnectionStatus
     * @param ConnectionStatusNormalizer $connectionStatusNormalizer
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        GetConnectionStatusHandler $getConnectionStatus,
        ConnectionStatusNormalizer $connectionStatusNormalizer
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getConfigurationHandler = $getConfigurationHandler;
        $this->getConnectionStatusHandler = $getConnectionStatus;
        $this->connectionStatusNormalizer = $connectionStatusNormalizer;
    }

    /**
     * @return Response
     */
    public function getAction(): Response
    {
        $configuration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
        $normalizedConfiguration = (null === $configuration) ? [] : $configuration->normalize();

        return new JsonResponse($normalizedConfiguration);
    }

    /**
     * @return Response
     */
    public function isActiveAction(): Response
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());

        return new JsonResponse($this->connectionStatusNormalizer->normalize($connectionStatus));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request): Response
    {
        $configurationFields = json_decode($request->getContent(), true);

        try {
            if (!isset($configurationFields['token']) && !is_string($configurationFields['token'])) {
                throw new \InvalidArgumentException();
            }

            $token = new Token($configurationFields['token']);
            $command = new ActivateConnectionCommand($token);
            $this->activateConnectionHandler->handle($command);
        } catch (InvalidConnectionConfigurationException $invalidConnection) {
            return new JsonResponse([
                'message' => 'akeneo_suggest_data.connection.flash.invalid',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'message' => 'akeneo_suggest_data.connection.flash.error',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'message' => 'akeneo_suggest_data.connection.flash.success',
        ]);
    }
}
