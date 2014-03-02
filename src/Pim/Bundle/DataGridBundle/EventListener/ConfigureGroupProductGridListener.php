<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\GroupColumnsConfigurator;

/**
 * Grid listener to configure column, filter and sorter based on attributes and business rules
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureGroupProductGridListener extends ConfigureProductGridListener
{
    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig)
    {
        $groupId = $this->request->get('id', null);
        if (!$groupId) {
            $groupId = $this->requestParams->get('currentGroup', null);
        }

        $em = $this->productManager->getEntityManager();
        $group = $em->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')->findOne($groupId);

        return new GroupColumnsConfigurator($datagridConfig, $this->confRegistry, $group);
    }
}
