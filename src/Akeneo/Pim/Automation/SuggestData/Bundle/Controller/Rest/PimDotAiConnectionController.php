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

use PimEnterprise\Component\SuggestData\Application\SuggestDataConnection;
use PimEnterprise\Component\SuggestData\Query\GetNormalizedConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimDotAiConnectionController
{
    /** @var SuggestDataConnection */
    private $suggestDataConnection;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param SuggestDataConnection      $suggestDataConnection
     * @param GetNormalizedConfiguration $getNormalizedConfiguration
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        SuggestDataConnection $suggestDataConnection,
        GetNormalizedConfiguration $getNormalizedConfiguration,
        TranslatorInterface $translator
    ) {
        $this->suggestDataConnection = $suggestDataConnection;
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

        return new JsonResponse($normalizedConfiguration);
    }
}
