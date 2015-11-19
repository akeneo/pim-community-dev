<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    /** @var EntityRepository */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param EntityRepository    $familyRepository
     * @param NormalizerInterface $normalizer
     */
    public function __construct(EntityRepository $familyRepository, NormalizerInterface $normalizer)
    {
        $this->familyRepository = $familyRepository;
        $this->normalizer       = $normalizer;
    }

    /**
     * Get the family collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $families = $this->familyRepository->findAll();

        $normalizedFamilies = [];
        foreach ($families as $family) {
            $normalizedFamilies[$family->getCode()] = $this->normalizer->normalize($family, 'json');
        }

        return new JsonResponse($normalizedFamilies);
    }
}
