<?php

namespace Akeneo\Pim\Enrichment\Bundle\Resolver;

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

    /** @var ContainerInterface */
    protected $container;

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
     * @return string|null
     */
    public function getFQCN($entityType)
    {
        try {
            $className = $this->container->getParameter(
                sprintf('pim_catalog.entity.%s.class', $entityType)
            );
        } catch (InvalidArgumentException $e) {
            $className = null;
        }

        return $className;
    }
}
