<?php
namespace Pim\Bundle\UIBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Build our menu based on knp menu bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Builder extends ContainerAware
{
    const MAIN_MENU_CODE = 'PIM_MAIN_MENU';

    /**
     * Create our main menu
     * @param FactoryInterface $factory
     * @param array $options
     * @return unknown
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem(self::MAIN_MENU_CODE);

        $menu->setChildrenAttribute('id', 'mainmenu');

        // first level items
        $firstLevelCssClass = 'first-level';
        $menu->addChild('Dashboard');
        $menu['Dashboard']->addChild('Home', array('route' => '_welcome'));

        $menu['Dashboard']->setAttribute('class', $firstLevelCssClass);
        $menu->addChild('Catalog');
        $menu['Catalog']->setAttribute('class', $firstLevelCssClass);
        $menu->addChild('Connectors');
        $menu['Connectors']->setAttribute('class', $firstLevelCssClass);
        $menu->addChild('Users');
        $menu['Users']->setAttribute('class', $firstLevelCssClass);

        // second level items
        $menu['Catalog']->addChild('Products', array('route' => 'akeneo_catalog_product_index'));
        $menu['Catalog']->addChild('Product types', array('route' => 'akeneo_catalog_producttype_index'));
        $menu['Catalog']->addChild('Product fields', array('route' => 'akeneo_catalog_productfield_index'));
        $menu['Connectors']->addChild('Icecat');
        $menu['Connectors']['Icecat']->addChild('Settings', array('route' => 'pim_connectoricecat_config_edit'));
        $menu['Connectors']['Icecat']->addChild('Suppliers list', array('route' => 'pim_connectoricecat_supplier_list'));
        $menu['Connectors']['Icecat']->addChild('Products list', array('route' => 'pim_connectoricecat_product_list'));

        // TODO get menu items from bundles (each can define its own items)

        // TODO : get from tree structure (get from database, so each bundle can declare its own items)
        /*
         * You can create a menu easily from a Tree structure (a nested set for example) by making it implement Knp\Menu\NodeInterface.
         * You will then be able to create the menu easily (assuming $node is the root node of your structure):
            $factory = new \Knp\Menu\MenuFactory();
            $menu = $factory->createFromNode($node);
         */

        return $menu;
    }
}