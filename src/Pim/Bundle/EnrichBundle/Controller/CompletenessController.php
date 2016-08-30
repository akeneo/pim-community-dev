<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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

    /**  @var EngineInterface */
    protected $templating;

    /**
     * @param CompletenessManager        $completenessManager
     * @param ProductRepositoryInterface $productRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param UserContext                $userContext
     * @param EngineInterface            $templating
     */
    public function __construct(
        CompletenessManager $completenessManager,
        ProductRepositoryInterface $productRepository,
        ChannelRepositoryInterface $channelRepository,
        UserContext $userContext,
        EngineInterface $templating
    ) {
        $this->completenessManager = $completenessManager;
        $this->productRepository = $productRepository;
        $this->channelRepository = $channelRepository;
        $this->userContext = $userContext;
        $this->templating = $templating;
    }

    /**
     * Displays completeness for a product
     *
     * @param int $id
     *
     * @return Response
     */
    public function completenessAction($id)
    {
        $product = $this->productRepository->getFullProduct($id);
        $channels = $this->channelRepository->getFullChannels();
        $locales = $this->userContext->getUserLocales();

        $completenesses = $this->completenessManager->getProductCompleteness(
            $product,
            $channels,
            $locales,
            $this->userContext->getCurrentLocale()->getCode()
        );

        return $this->templating->renderResponse(
            'PimEnrichBundle:Completeness:_completeness.html.twig',
            [
                'product'        => $product,
                'channels'       => $channels,
                'locales'        => $locales,
                'completenesses' => $completenesses
            ]
        );
    }
}
