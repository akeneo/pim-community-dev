<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
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

    /** @var UserContext */
    protected $userContext;

    /**
     * @param VersionRepositoryInterface $versionRepository
     * @param FQCNResolver               $FQCNResolver
     * @param NormalizerInterface        $normalizer
     * @param UserContext                $userContext
     */
    public function __construct(
        VersionRepositoryInterface $versionRepository,
        FQCNResolver $FQCNResolver,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ) {
        $this->versionRepository = $versionRepository;
        $this->FQCNResolver = $FQCNResolver;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
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
