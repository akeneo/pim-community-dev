<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\GroupColumnsConfigurator;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;

/**
 * Grid listener to configure column, filter and sorter based on attributes and business rules
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureGroupProductGridListener extends ConfigureProductGridListener
{
    /** @var GroupRepository */
    protected $groupRepository;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager
     * @param ConfigurationRegistry    $confRegistry
     * @param RequestParameters        $requestParams
     * @param SecurityContextInterface $securityContext
     * @param GroupRepository          $groupRepository
     */
    public function __construct(
        ProductManager $productManager,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams,
        SecurityContextInterface $securityContext,
        EntityRepository $datagridViewRepository,
        GroupRepository $groupRepository
    ) {
        parent::__construct(
            $productManager,
            $confRegistry,
            $requestParams,
            $securityContext,
            $datagridViewRepository,
            $groupRepository
        );

        $this->groupRepository = $groupRepository;
    }
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

        $group = $this->groupRepository->findOne($groupId);

        return new GroupColumnsConfigurator($datagridConfig, $this->confRegistry, $group);
    }
}
