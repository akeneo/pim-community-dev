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
    /**
     * Create our main menu
     * @param FactoryInterface $factory
     * @param array $options
     * @return unknown
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Dashboard', array('route' => '_welcome'));
        $menu->addChild('Product List', array('route' => 'akeneo_catalog_product_index'));


        $menu->addChild('Connectors');
        $menu['Connectors']->addChild('Supplier List', array('route' => 'strixos_icecatconnector_supplier_list'));

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