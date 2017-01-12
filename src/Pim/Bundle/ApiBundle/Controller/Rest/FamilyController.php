<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var FamilyRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param FamilyRepositoryInterface $repository
     * @param NormalizerInterface       $normalizer
     */
    public function __construct(
        FamilyRepositoryInterface $repository,
        NormalizerInterface $normalizer
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $family = $this->repository->findOneByIdentifier($code);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $code));
        }

        $familyStandard = $this->normalizer->normalize($family, 'standard');

        return new JsonResponse($familyStandard);
    }
}
