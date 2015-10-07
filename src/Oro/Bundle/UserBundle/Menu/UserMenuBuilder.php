<?php

namespace Oro\Bundle\UserBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class UserMenuBuilder implements BuilderInterface
{
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menu->setExtra('type', 'dropdown');

        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider');
        $menu->addChild(
            'Logout',
            array(
                'route' => 'oro_user_security_logout',
                'check_access' => false,
                'linkAttributes' => array(
                    'class' => 'no-hash'
                )
            )
        );
    }
}
