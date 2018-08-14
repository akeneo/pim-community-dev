<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping;

use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * ClassMetadata object factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClassMetadataFactory
{
    /**
     * Create an instance of ClassMetadata
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function createMetadata($class)
    {
        return new ClassMetadata($class);
    }
}
