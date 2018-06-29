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

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller\Rest;

use PimEnterprise\Component\SuggestData\Application\ActivateSuggestDataConnection;
use PimEnterprise\Component\SuggestData\Application\GetNormalizedConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConnectionController
{
    /** @var ActivateSuggestDataConnection */
    private $activateSuggestDataConnection;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param ActivateSuggestDataConnection $activateSuggestDataConnection
     * @param GetNormalizedConfiguration    $getNormalizedConfiguration
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        ActivateSuggestDataConnection $activateSuggestDataConnection,
        GetNormalizedConfiguration $getNormalizedConfiguration,
        TranslatorInterface $translator
    ) {
        $this->activateSuggestDataConnection = $activateSuggestDataConnection;
        $this->getNormalizedConfiguration = $getNormalizedConfiguration;
        $this->translator = $translator;
    }

    /**
     * @param string $code
     *
     * @return Response
     */
    public function getAction(string $code): Response
    {
        $normalizedConfiguration = $this->getNormalizedConfiguration->query($code);

        return new JsonResponse($normalizedConfiguration['configuration_fields']);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     */
    public function postAction(Request $request, string $code): Response
    {
        $configurationFields = json_decode($request->getContent(), true);

        $isActivated = $this->activateSuggestDataConnection->activate($code, $configurationFields);

        if (false === $isActivated) {
            return new JsonResponse([
                'successful' => $isActivated,
                'message' => $this->translator->trans('pimee_suggest_data.connection.pim_ai.error'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'successful' => $isActivated,
            'message' => $this->translator->trans('pimee_suggest_data.connection.pim_ai.success'),
        ]);
    }
}
