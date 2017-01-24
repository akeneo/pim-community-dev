<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller\Rest;

use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class Channel controller
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 */
class ChannelController
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $transformationRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    public function __construct(
        ChannelConfigurationRepositoryInterface $transformationRepository,
        NormalizerInterface $normalizer
    ) {
        $this->transformationRepository = $transformationRepository;
        $this->normalizer = $normalizer;
    }

    public function assetTransformationAction($id)
    {
        return new JsonResponse(
            $this->normalizer->normalize(
                (array) $this->transformationRepository
                    ->findOneByIdentifier($id),
                'standard'
            )
        );
    }
}
