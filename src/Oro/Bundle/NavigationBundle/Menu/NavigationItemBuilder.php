<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface;
use Oro\Bundle\NavigationBundle\Entity\Repository\NavigationRepositoryInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NavigationItemBuilder implements BuilderInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ItemFactory
     */
    private $factory;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager         $em
     * @param ItemFactory           $factory
     */
    public function __construct(TokenStorageInterface $tokenStorage, EntityManager $em, ItemFactory $factory)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->factory = $factory;
    }

    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array                   $options
     * @param string|null             $alias
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $menu->setExtra('type', $alias);
        if (is_object($user)) {
            /** @var $entity NavigationItemInterface */
            $entity = $this->factory->createItem($alias, []);

            /** @var $repo NavigationRepositoryInterface */
            $repo = $this->em->getRepository(get_class($entity));
            $items = $repo->getNavigationItems($user->getId(), $alias, $options);
            foreach ($items as $item) {
                $menu->addChild(
                    $alias . '_item_' . $item['id'],
                    [
                        'extras' => $item,
                        'uri'    => $item['url'],
                        'label'  => $item['title']
                    ]
                );
            }
        }
    }
}
