<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Columns configurator for products grid (used to associate products to groups)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupColumnsConfigurator extends ColumnsConfigurator
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var RequestStack */
    protected $requestStack;

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param ConfigurationRegistry    $registry
     * @param RequestParameters        $requestParams
     * @param GroupRepositoryInterface $groupRepository
     * @param RequestStack             $requestStack
     */
    public function __construct(
        ConfigurationRegistry $registry,
        RequestParameters $requestParams,
        GroupRepositoryInterface $groupRepository,
        RequestStack $requestStack
    ) {
        parent::__construct($registry);
        $this->requestParams = $requestParams;
        $this->groupRepository = $groupRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->preparePropertiesColumns();
        $this->prepareAttributesColumns();
        $this->sortColumns();
        $this->addColumns();
    }

    /**
     * @return null|Request
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return GroupInterface
     */
    protected function getGroup()
    {
        $groupId = $this->getRequest()->get('id', null);
        if (!$groupId) {
            $groupId = $this->requestParams->get('currentGroup', null);
        }

        $group = $this->groupRepository->find($groupId);

        return $group;
    }

    /**
     * Sort the columns
     */
    protected function sortColumns()
    {
        $inGroupColumn = [];
        if (isset($this->propertiesColumns['in_group'])) {
            $inGroupColumn['in_group'] = $this->propertiesColumns['in_group'];
            unset($this->propertiesColumns['in_group']);
        }

        $this->displayedColumns = $this->editableColumns + $inGroupColumn + $this->primaryColumns
            + $this->propertiesColumns;
    }
}
