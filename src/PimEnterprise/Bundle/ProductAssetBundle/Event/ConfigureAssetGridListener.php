<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;

/**
 * Grid listener to configure asset grid
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ConfigureAssetGridListener
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
