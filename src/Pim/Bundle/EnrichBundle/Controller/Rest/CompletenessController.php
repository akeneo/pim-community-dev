<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Completeness rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $completenessNormalizer;

    /** @var CompletenessCalculatorInterface */
    protected $completenessCalculator;

    /**
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $compNormalizer
     * @param CompletenessCalculatorInterface $completenessCalculator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $compNormalizer,
        CompletenessCalculatorInterface $completenessCalculator
    ) {
        $this->productRepository = $productRepository;
        $this->completenessNormalizer = $compNormalizer;
        $this->completenessCalculator = $completenessCalculator;
    }

    /**
     * Get completeness for a product
     *
     * @param int|string $id
     *
     * @return JSONResponse
     */
    public function getAction($id)
    {
        $product = $this->productRepository->find($id);
        if (null === $product->getFamily()) {
            return new JsonResponse();
        }

        $completenessCollection = $product->getCompletenesses();

        if ($completenessCollection->isEmpty()) {
            $newCompletenesses = $this->completenessCalculator->calculate($product);

            foreach ($newCompletenesses as $completeness) {
                $completenessCollection->add($completeness);
            }
        }

        return new JsonResponse($this->completenessNormalizer->normalize($completenessCollection, 'internal_api'));
    }
}
