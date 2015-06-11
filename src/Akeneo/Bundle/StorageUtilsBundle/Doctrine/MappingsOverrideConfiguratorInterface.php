<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Configuration;

/**
 * Configure the mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MappingsOverrideConfiguratorInterface
{
    /**
     * Configure the mappings of models that are override.
     *
     * Here is an example of mapping overrides (keys "original" and "override" are expected):
     * $mappingOverrides = [
     *      ['original' => 'Foo\Bar\Class', 'override' => 'Acme\Bar\Class'],
     *      ['original' => 'Foo\Baz\Class', 'override' => 'Acme\Baz\Class'],
     *  ]
     *
     * @param ClassMetadata $metadata
     * @param Configuration $configuration
     * @param array         $mappingOverrides
     *
     * @return ClassMetadata
     */
    public function configure(ClassMetadata $metadata, Configuration $configuration, array $mappingOverrides);
}
