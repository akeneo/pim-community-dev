<?php

namespace Pim\Bundle\EnrichBundle\Controller;

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
class VersioningRestController
{
    protected $versionRepository;
    protected $FQCNResolver;
    protected $normalizer;

    public function __construct(
        VersionRepositoryInterface $versionRepository,
        FQCNResolver $FQCNResolver,
        NormalizerInterface $normalizer
    ) {
        $this->versionRepository = $versionRepository;
        $this->FQCNResolver      = $FQCNResolver;
        $this->normalizer        = $normalizer;
    }

    public function getAction($entityType, $entityId)
    {
        var_dump(
            $this->normalizer->normalize(
                $this->versionRepository->getLogEntries($this->FQCNResolver->getFQCN($entityType), $entityId),
                'array'
            )
        ); die;
    }
}
