<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController
{
    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param AssociationTypeRepositoryInterface $associationTypeRepo
     * @param NormalizerInterface                $normalizer
     */
    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepo,
        NormalizerInterface $normalizer
    ) {
        $this->associationTypeRepo = $associationTypeRepo;
        $this->normalizer          = $normalizer;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $associationTypes = $this->associationTypeRepo->findAll();

        $data = $this->normalizer->normalize($associationTypes, 'internal_api');

        return new JsonResponse($data);
    }
}
