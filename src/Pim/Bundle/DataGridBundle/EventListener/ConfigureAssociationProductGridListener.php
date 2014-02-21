<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\AssociationProductColumnsConfigurator;

/**
 * Grid listener to configure columns for association product grid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureAssociationProductGridListener extends ConfigureFlexibleGridListener
{
    /**
     * {@inheritdoc}
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new AssociationProductColumnsConfigurator($datagridConfig, $this->confRegistry);
    }
}
