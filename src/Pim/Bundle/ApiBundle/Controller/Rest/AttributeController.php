<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var AttributeRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param NormalizerInterface          $normalizer
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
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
        $attribute = $this->repository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist.', $code));
        }

        $attributeStandard = $this->normalizer->normalize($attribute, 'standard');

        return new JsonResponse($attributeStandard);
    }
}
