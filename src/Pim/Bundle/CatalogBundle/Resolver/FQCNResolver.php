<?php

namespace Pim\Bundle\CatalogBundle\Resolver;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * FQCN Resolver
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FQCNResolver
{
    /** @var array */
    protected $classNames = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get FQCN for the given entity type
     *
     * @param string $entityType
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getFQCN($entityType)
    {
        return $this->container->getParameter(sprintf('pim_catalog.entity.%s.class', $entityType));
    }
}
