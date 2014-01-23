<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
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
     * @var SecurityContextInterface $securityContext
     */
    protected $securityContext;

    /**
     * @var AddLocaleListener $listener
     */
    protected $listener;

    /**
     * @var ProductManager $productManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param AddLocaleListener        $listener
     * @param ProductManager           $productManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AddLocaleListener $listener,
        ProductManager $productManager
    ) {
        $this->securityContext = $securityContext;
        $this->listener        = $listener;
        $this->productManager  = $productManager;
    }

    /**
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType() || null === $this->getUser()) {
            return;
        }

        $request = $event->getRequest();

        $this->configureTranslatableListener($request);
        $this->configureProductManager($request);
    }

    /**
     * Configure gedmo translatable locale
     *
     * @param Request $request
     */
    protected function configureTranslatableListener(Request $request)
    {
        $this->listener->setLocale($this->getDataLocale($request));
    }

    /**
     * Define locale and scope in ProductManager
     *
     * @param Request $request
     */
    protected function configureProductManager(Request $request)
    {
        $this->productManager->setLocale($this->getDataLocale($request));
        $this->productManager->setScope($this->getDataScope($request));
    }

    /**
     * Get data locale from request or user property
     *
     * @param Request $request
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getDataLocale(Request $request = null)
    {
        $dataLocale = $request->get('dataLocale');
        if ($dataLocale === null) {
            $dataLocale = $this->getCatalogLocale();
        }

        if (!$dataLocale) {
            throw new \Exception('User must have a catalog locale defined');
        }

        return $dataLocale;
    }

    /**
     * Get data scope from request or user property
     *
     * @param Request $request
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getDataScope(Request $request)
    {
        $dataScope = $request->get('dataScope');
        if ($dataScope === null) {
            $catalogScope = $this->getUser()->getCatalogScope();
            if ($catalogScope) {
                $dataScope = $catalogScope->getCode();
            }
        }
        if (!$dataScope) {
            throw new \Exception('User must have a catalog scope defined');
        }

        return $dataScope;
    }

    /**
     * Get user catalog locale
     *
     * @return string
     */
    protected function getCatalogLocale()
    {
        $catalogLocale = $this->getUser()->getCatalogLocale();

        return $catalogLocale ? $catalogLocale->getCode() : null;
    }

    /**
     * Get the authenticate user
     *
     * @return NULL|Oro\Bundle\UserBundle\Entity\User
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
