<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Controller for completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessController
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
     * @var EngineInterface
     */
    protected $templating;

    /**
     * Constructor
     *
     * @param CompletenessManager $completenessManager
     * @param ProductManager      $productManager
     * @param ChannelManager      $channelManager
     * @param UserContext         $userContext
     * @param EngineInterface     $templating
     */
    public function __construct(
        CompletenessManager $completenessManager,
        ProductManager $productManager,
        ChannelManager $channelManager,
        UserContext $userContext,
        EngineInterface $templating
    ) {
        $this->completenessManager = $completenessManager;
        $this->productManager      = $productManager;
        $this->channelManager      = $channelManager;
        $this->userContext         = $userContext;
        $this->templating          = $templating;
    }

    /**
     * Displays completeness for a product
     *
     * @param integer $id
     *
     * @return Response
     */
    public function completenessAction($id)
    {
        $product = $this->productManager->getFlexibleRepository()->getFullProduct($id);
        $channels = $this->channelManager->getFullChannels();
        $locales = $this->userContext->getUserLocales();

        $completenesses = $this->completenessManager->getProductCompleteness(
            $product,
            $channels,
            $locales,
            $this->userContext->getCurrentLocale()
        );

        return $this->templating->renderResponse(
            'PimEnrichBundle:Completeness:_completeness.html.twig',
            array(
                'product'           => $product,
                'channels'          => $channels,
                'locales'           => $locales,
                'completenesses'    => $completenesses
            )
        );
    }
}
