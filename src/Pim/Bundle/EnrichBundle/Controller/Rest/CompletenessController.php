<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
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
    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var NormalizerInterface */
    protected $completenessNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param CompletenessManager        $completenessManager
     * @param ProductRepositoryInterface $productRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param UserContext                $userContext
     * @param NormalizerInterface        $completenessNormalizer
     * @param CollectionFilterInterface  $collectionFilter
     */
    public function __construct(
        CompletenessManager $completenessManager,
        ProductRepositoryInterface $productRepository,
        ChannelRepositoryInterface $channelRepository,
        UserContext $userContext,
        NormalizerInterface $completenessNormalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->completenessManager    = $completenessManager;
        $this->productRepository      = $productRepository;
        $this->channelRepository      = $channelRepository;
        $this->userContext            = $userContext;
        $this->completenessNormalizer = $completenessNormalizer;
        $this->collectionFilter       = $collectionFilter;
    }

    /**
     * Get completeness for a product
     *
     * @param int $id
     *
     * @return JSONResponse
     */
    public function getAction($id)
    {
        $product = $this->productRepository->getFullProduct($id);
        if (null === $product->getFamily()) {
            return new JsonResponse();
        }

        $this->completenessManager->generateMissingForProduct($product);

        $channels = $this->channelRepository->getFullChannels();
        $locales  = $this->userContext->getUserLocales();

        $filteredLocales = $this->collectionFilter->filterCollection($locales, 'pim.internal_api.locale.view');

        $completenesses = $this->completenessManager->getProductCompleteness(
            $product,
            $channels,
            $filteredLocales,
            $this->userContext->getCurrentLocale()->getCode()
        );

        return new JsonResponse($this->completenessNormalizer->normalize($completenesses, 'internal_api'));
    }
}
