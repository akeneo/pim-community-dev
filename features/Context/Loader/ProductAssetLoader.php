<?php

namespace Context\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Process\PhpExecutableFinder;

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
     * @param string        $environment
     */
    public function load(ObjectManager $manager, $environment)
    {
        $pathFinder = new PhpExecutableFinder();

        exec(
            sprintf(
                '%s %s/01_create_channel_configurations.php %s',
                $pathFinder->find(),
                realpath(__DIR__ . '/../../../src/PimEnterprise/Bundle/ProductAssetBundle/dataset'),
                $environment
            )
        );

        exec(
            sprintf(
                '%s %s/02_create_assets.php %s',
                $pathFinder->find(),
                realpath(__DIR__ . '/../../../src/PimEnterprise/Bundle/ProductAssetBundle/dataset'),
                $environment
            )
        );

        $stmt = $manager->getConnection()->prepare($this->getProductAssetSql());
        $stmt->execute();
    }

    private function getProductAssetSql()
    {
        $path = __DIR__ . '/../../../src/PimEnterprise/Bundle/ProductAssetBundle/Resources/fixtures/product_assets.sql';

        return file_get_contents(realpath($path));
    }
}
