<?php

namespace Oro\Bundle\NavigationBundle\Event;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\User;

class ResponseHistoryListener
{
    /**
     * @var User|String
     */
    protected $user = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container
    ) {
        $this->user = !$tokenStorage->getToken() || is_string($tokenStorage->getToken()->getUser())
            ? null : $tokenStorage->getToken()->getUser();
        $this->container = $container;
    }

    /**
     * Process onResponse event, updates user history information
     *
     * @param  FilterResponseEvent $event
     *
     * @return bool|void
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // Do not do anything
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // do not process requests other than in html format
        // with 200 OK status using GET method and not _internal and _wdt
        if (!$this->matchRequest($response, $request)) {
            return false;
        }

        $postArray = [
            'url'  => $request->getRequestUri(),
            'user' => $this->user,
        ];

        /** @var $historyItem  NavigationHistoryItem */
        $historyItem = $this->getEntityManager()->getRepository('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem')
            ->findOneBy($postArray);
        if (!$historyItem) {
            /** @var $historyItem \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
            $historyItem = $this->getItemFactory()->createItem(
                NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE,
                $postArray
            );
        }

        $titleService = $this->getTitleService();
        if (!empty($historyItem->getCode())) {
            $titleService->setData(['params' => ['%code%' => $historyItem->getCode()]]);
        }
        $historyItem->setTitle($this->getTitleService()->getSerialized());

        // force update
        $historyItem->doUpdate();

        $this->getEntityManager()->persist($historyItem);
        $this->getEntityManager()->flush($historyItem);

        return true;
    }

    /**
     * Is request valid for adding to history
     *
     * @param  Response $response
     * @param  Request  $request
     *
     * @return bool
     */
    private function matchRequest(Response $response, Request $request)
    {
        $route = $request->get('_route');

        return !($response->getStatusCode() != 200
            || $request->getRequestFormat() != 'html'
            || $request->getMethod() != 'GET'
            || ($request->isXmlHttpRequest()
                && !$request->headers->get(ResponseHashnavListener::HASH_NAVIGATION_HEADER))
            || $route[0] == '_'
            || is_null($this->user)
            || in_array($route, $this->getForbiddenRoutes()));
    }

    /**
     * @return EntityManager
     */
    final protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return TitleServiceInterface
     */
    final protected function getTitleService()
    {
        return $this->container->get('oro_navigation.title_service');
    }

    /**
     * @return ItemFactory
     */
    final protected function getItemFactory()
    {
        return $this->container->get('oro_navigation.item.factory');
    }

    /**
     * @return string[]
     */
    final protected function getForbiddenRoutes()
    {
        return $this->container->getParameter('oro_navigation.history.forbidden_routes');
    }
}
