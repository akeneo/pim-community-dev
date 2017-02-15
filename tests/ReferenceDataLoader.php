<?php

namespace Akeneo\Test\Integration;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Integration test loader for reference data
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
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
        if (0 === (int) $query->getSingleScalarResult()) {
            $stmt = $manager->getConnection()->prepare($this->getFabricsSql());
            $stmt->execute();
        }

        $query = $manager->createQuery('SELECT COUNT(c) FROM \Acme\Bundle\AppBundle\Entity\Color c');
        if (0 === (int) $query->getSingleScalarResult()) {
            $stmt = $manager->getConnection()->prepare($this->getColorSql());
            $stmt->execute();
        }
    }

    private function getFabricsSql()
    {
        $path = __DIR__ . '/../src/Acme/Bundle/AppBundle/Resources/fixtures/fabrics.sql';

        return file_get_contents(realpath($path));
    }

    private function getColorSql()
    {
        $path = __DIR__ . '/../src/Acme/Bundle/AppBundle/Resources/fixtures/colors.sql';

        return file_get_contents(realpath($path));
    }
}
