<?php

namespace Context\Loader;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Loader for product assets
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssetLoader
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $query = $manager->createQuery('SELECT COUNT(a) FROM \PimEnterprise\Component\ProductAsset\Model\Asset a');
        if (0 == $query->getSingleScalarResult()) {
            $stmt = $manager->getConnection()->prepare($this->getProductAssetSql());
            $stmt->execute();
        }
    }

    private function getProductAssetSql()
    {
        $path = __DIR__ . '/../../../src/PimEnterprise/Bundle/ProductAssetBundle/Resources/fixtures/product_assets.sql';

        return file_get_contents(realpath($path));
    }
}
