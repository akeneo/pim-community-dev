<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader as BaseYamlReader;

/**
 * Yaml reader for data fixtures
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReader extends BaseYamlReader
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'system' => true
            ],
            'multiple' => [
                'system' => true
            ],
        ];
    }
}
