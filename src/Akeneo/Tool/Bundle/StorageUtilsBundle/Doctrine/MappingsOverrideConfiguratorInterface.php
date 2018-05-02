<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Configure the mappings of the metadata classes that are override.
 *
 * Let's say we override the model Foo\Bar\Model by our custom class Acme\Bar\Model.
 * All the associations of Foo\Bar\Model will be removed and injected in the metadata of Acme\Bar\Model.
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
     *      ['original' => 'Foo\Bar\Model', 'override' => 'Acme\Bar\Model'],
     *      ['original' => 'Foo\Baz\Model', 'override' => 'Acme\Baz\Model'],
     *  ]
     *
     * @param ClassMetadata $metadata
     * @param mixed         $configuration
     * @param array         $mappingOverrides
     *
     * @return ClassMetadata
     */
    public function configure(ClassMetadata $metadata, array $mappingOverrides, $configuration);
}
