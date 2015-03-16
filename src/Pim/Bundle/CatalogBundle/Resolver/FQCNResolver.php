<?php

namespace Pim\Bundle\CatalogBundle\Resolver;

/**
 * FQCN Resolver
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FQCNResolver
{
    protected $classNames = [];

    public function getFQCN($entityType)
    {
        if (!isset($this->classNames[$entityType])) {
            throw \LogicException(sprintf('The class name for %s is unknown', $entityType));
        }

        return $this->classNames[$entityType];
    }

    public function setFQCN($entityType, $className)
    {
        $this->classNames[$entityType] = $className;
    }
}
