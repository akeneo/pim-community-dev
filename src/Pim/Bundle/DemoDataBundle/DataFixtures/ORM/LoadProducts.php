<?php
namespace Pim\Bundle\DemoDataBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\DemoDataBundle\DataFixtures\Base\AbstractLoadProducts;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

/**
 * Load ORM products samples
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProducts extends AbstractLoadProducts
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->productManager = new ProductManager($manager);
        parent::load($manager);
    }

}
