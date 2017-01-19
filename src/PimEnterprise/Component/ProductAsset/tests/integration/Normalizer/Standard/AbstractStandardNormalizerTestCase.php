<?php

namespace PimEnterprise\Component\ProductAsset\tests\integration\Normalizer\Standard;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractStandardNormalizerTestCase extends TestCase
{
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
