<?php

namespace Pim\Bundle\CatalogBundle\Resolver;

/**
 * FQCN Resolver
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * //TODO: I don't like this object at all
 * //TODO: and even if we really need it, that should not be here
 */
class FQCNResolver
{
    /** @var array */
    protected $classNames = [];

    /**
     * Get FQCN for the givent entity type
     *
     * @param string $entityType
     *
     * @throws \LogicException
     *
     * @return string
     */
    public function getFQCN($entityType)
    {
        if (!isset($this->classNames[$entityType])) {
            throw new \LogicException(sprintf('The class name for %s is unknown', $entityType));
        }

        return $this->classNames[$entityType];
    }

    /**
     * Set the FCQN for the given entity type
     *
     * @param string $entityType
     * @param string $className
     */
    public function setFQCN($entityType, $className)
    {
        $this->classNames[$entityType] = $className;
    }
}
