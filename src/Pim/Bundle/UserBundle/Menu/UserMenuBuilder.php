<?php

namespace Pim\Bundle\UserBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

/**
 * Class UserMenuBuilder
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserMenuBuilder implements BuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $menu->setExtra('type', 'dropdown');

        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider');

        $menu->addChild(
            'Logout',
            [
                'route'          => 'pim_user_security_logout',
                'check_access'   => false,
                'linkAttributes' => [
                    'class' => 'no-hash'
                ]
            ]
        );
    }
}
