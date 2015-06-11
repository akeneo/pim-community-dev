<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
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
class CompletenessRestController
{
    /**
     * @var CompletenessManager
     */
    protected $completenessManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var NormalizerInterface
     */
    protected $completenessNormalizer;

    /**
     * @var CollectionFilterInterface
     */
    protected $collectionFilter;

    /**
     * Constructor
     *
     * @param CompletenessManager       $completenessManager
     * @param ProductManager            $productManager
     * @param ChannelManager            $channelManager
     * @param UserContext               $userContext
     * @param NormalizerInterface       $completenessNormalizer
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        CompletenessManager $completenessManager,
        ProductManager $productManager,
        ChannelManager $channelManager,
        UserContext $userContext,
        NormalizerInterface $completenessNormalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->completenessManager    = $completenessManager;
        $this->productManager         = $productManager;
        $this->channelManager         = $channelManager;
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
        $product = $this->productManager->getProductRepository()->getFullProduct($id);
        $this->completenessManager->generateMissingForProduct($product);

        $channels = $this->channelManager->getFullChannels();
        $locales = $this->userContext->getUserLocales();

        $fitleredLocales = $this->collectionFilter->filterCollection($locales, 'pim:internal_api:locale:view');

        $completenesses = $this->completenessManager->getProductCompleteness(
            $product,
            $channels,
            $fitleredLocales,
            $this->userContext->getCurrentLocale()->getCode()
        );

        return new JsonResponse($this->completenessNormalizer->normalize($completenesses, 'internal_api'));
    }
}
