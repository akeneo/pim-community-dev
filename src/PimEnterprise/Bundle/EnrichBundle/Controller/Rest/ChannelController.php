<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller\Rest;

use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Channel controller
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelController
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $transformationRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ChannelConfigurationRepositoryInterface $transformationRepository
     * @param NormalizerInterface                     $normalizer
     */
    public function __construct(
        ChannelConfigurationRepositoryInterface $transformationRepository,
        NormalizerInterface $normalizer
    ) {
        $this->transformationRepository = $transformationRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * Asset transformation action
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function assetTransformationAction($id)
    {
        $assetTranformation = $this->transformationRepository
            ->findOneByIdentifier($id);

        return new JsonResponse(
            $this->normalizer->normalize(
                $assetTranformation ?
                    $assetTranformation->getConfiguration() : [],
                'internal_api'
            )
        );
    }
}
