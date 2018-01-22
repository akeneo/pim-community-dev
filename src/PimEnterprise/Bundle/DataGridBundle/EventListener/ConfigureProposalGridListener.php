<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;

/**
 * Grid listener to configure proposal grid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ConfigureProposalGridListener
{
    /** @var ConfiguratorInterface */
    private $contextConfigurator;

    /** @var FiltersConfigurator */
    private $filtersConfigurator;

    /**
     * @param ConfiguratorInterface $contextConfigurator
     * @param FiltersConfigurator   $filtersConfigurator
     */
    public function __construct(
        ConfiguratorInterface $contextConfigurator,
        FiltersConfigurator $filtersConfigurator
    ) {
        $this->contextConfigurator = $contextConfigurator;
        $this->filtersConfigurator = $filtersConfigurator;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->contextConfigurator->configure($datagridConfig);
        $this->filtersConfigurator->configure($datagridConfig);
    }
}
