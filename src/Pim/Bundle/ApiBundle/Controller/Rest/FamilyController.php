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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        //@TODO limit will be set in configuration in an other PR
        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);

        //@TODO add parameterValidator to validate limit and page

        $offset = $limit * ($page - 1);

        $families = $this->repository->findBy([], [], $limit, $offset);

        $familiesStandard = $this->normalizer->normalize($families, 'external_api');

        //@TODO use paginate method before return results

        return new JsonResponse($familiesStandard);
    }
}
