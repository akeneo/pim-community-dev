<?php

namespace Context\Loader;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Loader for reference data
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataLoader
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $query = $manager->createQuery('SELECT COUNT(f) FROM \Acme\Bundle\AppBundle\Entity\Fabric f');
        if (0 === $query->getSingleScalarResult()) {
            $stmt = $manager->getConnection()->prepare($this->getFabricsSql());
            $stmt->execute();
        }

        $query = $manager->createQuery('SELECT COUNT(c) FROM \Acme\Bundle\AppBundle\Entity\Color c');
        if (0 === $query->getSingleScalarResult()) {
            $stmt = $manager->getConnection()->prepare($this->getColorSql());
            $stmt->execute();
        }
    }

    private function getFabricsSql()
    {
        $path = __DIR__ . '/../../../src/Acme/Bundle/AppBundle/Resources/fixtures/fabrics.sql';

        return file_get_contents(realpath($path));
    }

    private function getColorSql()
    {
        $path = __DIR__ . '/../../../src/Acme/Bundle/AppBundle/Resources/fixtures/colors.sql';

        return file_get_contents(realpath($path));
    }
}
