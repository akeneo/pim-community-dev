<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRestController
{
    protected $productRepository;
    protected $normalizer;

    public function __construct(ProductRepositoryInterface $productRepository, NormalizerInterface $normalizer)
    {
        $this->productRepository = $productRepository;
        $this->normalizer        = $normalizer;
    }

    public function getAction($id)
    {
        $product = $this->productRepository->findOneById($id);

        return new JsonResponse($this->normalizer->normalize($product, 'json'));
    }
}
