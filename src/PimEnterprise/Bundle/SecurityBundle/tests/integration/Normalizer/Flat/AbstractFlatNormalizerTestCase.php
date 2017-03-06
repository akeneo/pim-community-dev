<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Normalizer\Flat;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\TestEnterprise\Integration\PermissionCleaner;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFlatNormalizerTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration();
        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $permissionCleaner = new PermissionCleaner(static::$kernel);
            $permissionCleaner->cleanPermission(static::$kernel);
        }
    }

    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .    DIRECTORY_SEPARATOR . 'technical'
            ],
            false
        );
    }
}
