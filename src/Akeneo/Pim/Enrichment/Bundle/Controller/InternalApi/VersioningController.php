<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Ramsey\Uuid\Uuid;
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
    public function __construct(
        private readonly VersionRepositoryInterface $versionRepository,
        private readonly FQCNResolver $FQCNResolver,
        private readonly NormalizerInterface $normalizer,
        private readonly UserContext $userContext
    ) {
    }

    /**
     * Get the history of the given entity type with the given entityId
     *
     * @param string $entityType
     * @param string $entityId This is an id OR a uuid
     *
     * @return JSONResponse
     */
    public function getAction($entityType, $entityId)
    {
        if (Uuid::isValid($entityId)) {
            $entityUuid = Uuid::fromString($entityId);
            $entityId = null;
        } else {
            $entityUuid = null;
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $this->versionRepository->getLogEntries($this->FQCNResolver->getFQCN($entityType), $entityId, $entityUuid),
                'internal_api'
            )
        );
    }
}
