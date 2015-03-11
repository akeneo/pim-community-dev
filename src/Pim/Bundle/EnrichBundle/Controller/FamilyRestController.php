<?php

namespace Pim\Bundle\EnrichBundle\Controller;

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
class FamilyRestController
{
    protected $familyRepository;
    protected $normalizer;

    public function __construct(EntityRepository $familyRepository, NormalizerInterface $normalizer)
    {
        $this->familyRepository = $familyRepository;
        $this->normalizer       = $normalizer;
    }

    public function indexAction()
    {
        $familys = $this->familyRepository->findAll();

        $normalizedAttributes = [];
        foreach ($familys as $family) {
            $normalizedAttributes[$family->getCode()] = $this->normalizer->normalize($family, 'json');
        }

        return new JsonResponse($normalizedAttributes);
    }

    public function getAction($id)
    {
        $family = $this->familyRepository->findOneById($id);

        return new JsonResponse($this->normalizer->normalize($family, 'json'));
    }
}
