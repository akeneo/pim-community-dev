<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilySearchableRepository;
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
 *
 * TODO: PIM-5194: to rework on master
 */
class FamilyController
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FamilySearchableRepository */
    protected $familySearchableRepo;

    /**
     * @param FamilyRepositoryInterface  $familyRepository
     * @param NormalizerInterface        $normalizer
     * @param FamilySearchableRepository $familySearchableRepo
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $normalizer,
        FamilySearchableRepository $familySearchableRepo = null
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
        #TODO: PIM-5194: to rework on master, drop the if condition
        if (null !== $this->familySearchableRepo) {
            $query  = $request->query;
            $search = $query->get('search');

            $families = $this->familySearchableRepo->findBySearch($search, $query->get('options', []));
        } else {
            $families = $this->familyRepository->findAll();
        }

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
