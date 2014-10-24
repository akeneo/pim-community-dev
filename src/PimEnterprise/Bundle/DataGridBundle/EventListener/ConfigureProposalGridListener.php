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
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;

/**
 * Grid listener to configure proposal grid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ConfigureProposalGridListener
{
    /** @var ConfiguratorInterface */
    protected $contextConfigurator;

    /**
     * Constructor
     *
     * @param ConfiguratorInterface $contextConfigurator
     */
    public function __construct(ConfiguratorInterface $contextConfigurator)
    {
        $this->contextConfigurator = $contextConfigurator;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->contextConfigurator->configure($datagridConfig);
    }
}
