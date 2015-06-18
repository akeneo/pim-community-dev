<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Resolver\FQCNResolver;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Versioning controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersioningController
{
    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var FQCNResolver */
    protected $FQCNResolver;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param VersionRepositoryInterface $versionRepository
     * @param FQCNResolver               $FQCNResolver
     * @param NormalizerInterface        $normalizer
     */
    public function __construct(
        VersionRepositoryInterface $versionRepository,
        FQCNResolver $FQCNResolver,
        NormalizerInterface $normalizer
    ) {
        $this->versionRepository = $versionRepository;
        $this->FQCNResolver      = $FQCNResolver;
        $this->normalizer        = $normalizer;
    }

    /**
     * Get the history of the given entity type with the given entityId
     *
     * @param string $entityType
     * @param string $entityId
     *
     * @return JSONResponse
     */
    public function getAction($entityType, $entityId)
    {
        return new JsonResponse(
            $this->normalizer->normalize(
                $this->versionRepository->getLogEntries($this->FQCNResolver->getFQCN($entityType), $entityId),
                'internal_api'
            )
        );
    }
}
