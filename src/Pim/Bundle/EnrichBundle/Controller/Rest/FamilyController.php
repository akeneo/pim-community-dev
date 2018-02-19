<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilySearchableRepository;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
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
        FamilySearchableRepository $familySearchableRepo
    ) {
        $this->familyRepository = $familyRepository;
        $this->normalizer = $normalizer;
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
        $options = $request->query->get('options', ['limit' => 20]);

        if ($request->query->has('identifiers')) {
            $options = $request->query->get('options');
            $options['identifiers'] = explode(',', $request->query->get('identifiers'));
        }

        $families = $this->familySearchableRepo->findBySearch(
            $request->query->get('search'),
            $options
        );

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
