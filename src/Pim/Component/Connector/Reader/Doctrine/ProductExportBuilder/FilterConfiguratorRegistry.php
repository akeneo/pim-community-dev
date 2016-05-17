<?php

namespace Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder;

/**
 * Registry of all filter configurators.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterConfiguratorRegistry
{
    /** @var FilterConfiguratorInterface[] */
    protected $configurators = [];

    /**
     * @param FilterConfiguratorInterface $configurator
     */
    public function register(FilterConfiguratorInterface $configurator)
    {
        $this->configurators[] = $configurator;
    }

    /**
     * @return FilterConfiguratorInterface[]
     */
    public function all()
    {
        return $this->configurators;
    }
}
