<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class NavigationListener
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var EntityManager|null
     */
    protected $em = null;

    /** @var ConfigProvider $entityConfigProvider */
    protected $entityConfigProvider = null;

    /**
     * @param SecurityFacade $securityFacade
     * @param EntityManager  $entityManager
     * @param ConfigProvider $entityConfigProvider
     */
    public function __construct(
        SecurityFacade $securityFacade,
        EntityManager $entityManager,
        ConfigProvider $entityConfigProvider
    ) {
        $this->securityFacade       = $securityFacade;
        $this->em                   = $entityManager;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $menu     = $event->getMenu();
        $children = array();

        $entitiesMenuItem = $menu->getChild('system_tab')->getChild('entities_list');
        if ($entitiesMenuItem) {

            sort($children);
            foreach ($children as $child) {
                $entitiesMenuItem->addChild($child['label'], $child['options']);
            }
        }

    }
}
