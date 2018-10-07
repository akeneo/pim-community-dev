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
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\ActivateSuggestDataConnection;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetNormalizedConfiguration;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetSuggestDataConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi\ConnectionStatusNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConnectionController
{
    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /** @var GetSuggestDataConnectionStatus */
    private $getSuggestDataConnectionStatus;

    /** @var ConnectionStatusNormalizer */
    private $connectionStatusNormalizer;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param ActivateConnectionHandler $activateConnectionHandler
     * @param GetNormalizedConfiguration $getNormalizedConfiguration
     * @param GetSuggestDataConnectionStatus $getSuggestDataConnectionStatus
     * @param ConnectionStatusNormalizer $connectionStatusNormalizer
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetNormalizedConfiguration $getNormalizedConfiguration,
        GetSuggestDataConnectionStatus $getSuggestDataConnectionStatus,
        ConnectionStatusNormalizer $connectionStatusNormalizer,
        TranslatorInterface $translator
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getNormalizedConfiguration = $getNormalizedConfiguration;
        $this->getSuggestDataConnectionStatus = $getSuggestDataConnectionStatus;
        $this->connectionStatusNormalizer = $connectionStatusNormalizer;
        $this->translator = $translator;
    }

    /**
     * @return Response
     */
    public function getAction(): Response
    {
        $normalizedConfiguration = $this->getNormalizedConfiguration->retrieve();

        return new JsonResponse($normalizedConfiguration);
    }

    /**
     * @return Response
     */
    public function isActiveAction(): Response
    {
        $connectionStatus = $this->getSuggestDataConnectionStatus->getStatus();

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
            $command = new ActivateConnectionCommand($configurationFields);
            $this->saveConfigurationHandler->handle($command);

            //$this->activateSuggestDataConnection->activate($configurationFields);
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
