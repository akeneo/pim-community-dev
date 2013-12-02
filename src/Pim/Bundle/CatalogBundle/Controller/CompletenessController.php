<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @var LocaleManager
     */
    protected $localeManager;

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
     * @param LocaleManager       $localeManager
     * @param EngineInterface     $templating
     */
    public function __construct(
        CompletenessManager $completenessManager,
        ProductManager $productManager,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        EngineInterface $templating
    ) {
        $this->completenessManager = $completenessManager;
        $this->productManager = $productManager;
        $this->channelManager = $channelManager;
        $this->localeManager = $localeManager;
        $this->templating = $templating;
    }

    /**
     * Displays completeness for a product
     *
     * @param  int      $id
     * @return Response
     */
    public function completenessAction($id)
    {
        $product = $this->productManager->getFlexibleRepository()->find($id);

        $channels = $this->channelManager->getChannels();
        $locales = $this->localeManager->getUserLocales();
        $completenesses = $this->completenessManager->getProductCompleteness(
            $product,
            $channels,
            $locales,
            $this->localeManager->getCurrentLocale()
        );

        return $this->templating->renderResponse(
            'PimCatalogBundle:Completeness:_completeness.html.twig',
            array(
                'product'           => $product,
                'channels'          => $channels,
                'locales'           => $locales,
                'completenesses'    => $completenesses
            )
        );
    }
}
