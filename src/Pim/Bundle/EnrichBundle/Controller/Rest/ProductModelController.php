<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelController
{
    /** @var NormalizerInterface */
    protected $productModelNormalizer;

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param NormalizerInterface             $productModelNormalizer
     * @param UserContext                     $userContext
     * @param ObjectFilterInterface           $objectFilter
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        NormalizerInterface $productModelNormalizer,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelNormalizer = $productModelNormalizer;
        $this->userContext            = $userContext;
        $this->objectFilter           = $objectFilter;
    }

    /**
     * @param int $id Product model id
     *
     * @throws NotFoundHttpException If product model is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function getAction(int $id): JsonResponse
    {
        $productModel = $this->productModelRepository->find($id);
        $cantView = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view');

        if (null === $productModel || true === $cantView) {
            throw new NotFoundHttpException(
                sprintf('Product model with id %s could not be found.', $id)
            );
        }

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];

        $normalizedProductModel = $this->productModelNormalizer->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        );

        return new JsonResponse($normalizedProductModel);
    }
}
