<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

class NavigationListener
{
    /**
     * @var EntityManager|null
     */
    protected $em = null;

    /** @var ConfigProvider $entityConfigProvider */
    protected $entityConfigProvider = null;

    /**
     * @param EntityManager $entityManager
     * @param ConfigProvider $entityConfigProvider
     */
    public function __construct(EntityManager $entityManager, ConfigProvider $entityConfigProvider)
    {
        $this->em                   = $entityManager;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $menu   = $event->getMenu();
        $childs = array();

        $entitiesMenuItem = $menu->getChild('system_tab')->getChild('entities_list');
        if ($entitiesMenuItem) {
            /** @var EntityConfigModel $entities */
            $entities = $this->em->getRepository(EntityConfigModel::ENTITY_NAME)->findAll();
            if ($entities) {
                foreach ($entities as $entity) {
                    $config = $this->entityConfigProvider->getConfig($entity->getClassname());

                    $childs[$config->get('label')] = array(
                        'label'   => $config->get('label'),
                        'options' => array(
                            'label'   => $config->get('label') . '<i class="' . $config->get(
                                'icon'
                            ) . ' hide-text pull-right"></i>',
                            'route'           => 'oro_entity_index',
                            'routeParameters' => array(
                                'id' => str_replace('\\', '_', $entity->getId())
                            ),
                            'extras'          => array(
                                'safe_label' => true,
                                //'icon' => $config->get('icon'),
                            ),
                        )
                    );
                }

                sort($childs);
                foreach ($childs as $child) {
                    $entitiesMenuItem->addChild($child['label'], $child['options']);
                }
            }
        }
    }
}
