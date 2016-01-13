<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SearchableRepositoryInterface */
    protected $familySearchableRepo;

    /**
     * @param FamilyRepositoryInterface     $familyRepository
     * @param NormalizerInterface           $normalizer
     * @param SearchableRepositoryInterface $familySearchableRepo
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $normalizer,
        SearchableRepositoryInterface $familySearchableRepo
    ) {
        $this->familyRepository     = $familyRepository;
        $this->normalizer           = $normalizer;
        $this->familySearchableRepo = $familySearchableRepo;
    }

    /**
     * Get the family collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $query  = $request->query;
        $search = $query->get('search');

        $families = $this->familySearchableRepo->findBySearch($search, $query->get('options', []));

        $normalizedFamilies = [];
        foreach ($families as $family) {
            $normalizedFamilies[$family->getCode()] = $this->normalizer->normalize($family, 'json');
        }

        return new JsonResponse($normalizedFamilies);
    }

    /**
     * Get a single family
     *
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $family = $this->familyRepository->findOneByIdentifier($identifier);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize($family, 'json'));
    }
}
