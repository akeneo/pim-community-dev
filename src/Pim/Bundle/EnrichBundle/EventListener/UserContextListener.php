<?php

namespace Pim\Bundle\EnrichBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener;

/**
 * User context listener
 * - Define the locale and the scope for the product manager
 * - Define the locale used by the translatable listener
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContextListener implements EventSubscriberInterface
{
     /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var AddLocaleListener
     */
    protected $listener;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param AddLocaleListener        $listener
     * @param ProductManager           $productManager
     * @param UserContext              $userContext
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AddLocaleListener $listener,
        ProductManager $productManager,
        UserContext $userContext
    ) {
        $this->securityContext = $securityContext;
        $this->listener        = $listener;
        $this->productManager  = $productManager;
        $this->userContext     = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType() || null === $this->securityContext->getToken()) {
            return;
        }

        // If user doesn't have access to any activated locales, skip configuring the listener and productmanager
        try {
            $this->configureTranslatableListener();
            $this->configureProductManager();
        } catch (\LogicException $e) {
        }
    }

    /**
     * Configure gedmo translatable locale
     */
    protected function configureTranslatableListener()
    {
        $this->listener->setLocale($this->userContext->getCurrentLocaleCode());
    }

    /**
     * Define locale and scope in ProductManager
     */
    protected function configureProductManager()
    {
        $this->productManager->setLocale($this->userContext->getCurrentLocaleCode());
        $this->productManager->setScope($this->userContext->getUserChannelCode());
    }
}
