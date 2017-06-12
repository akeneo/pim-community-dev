<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param array
     */
    protected $axisColumns;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param ConfigurationRegistry    $registry
     * @param RequestParameters        $requestParams
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        RequestParameters $requestParams,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($registry);
        $this->requestParams = $requestParams;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->preparePropertiesColumns();
        $this->prepareAttributesColumns();
        $this->prepareAxisColumns();
        $this->sortColumns();
        $this->addColumns();
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return GroupInterface
     */
    protected function getGroup()
    {
        $groupId = $this->request->get('id', null);
        if (!$groupId) {
            $groupId = $this->requestParams->get('currentGroup', null);
        }

        $group = $this->groupRepository->find($groupId);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAxisColumns()
    {
        $path = sprintf(self::SOURCE_PATH, self::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $axisCodes = array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $this->getGroup()->getAxisAttributes()->toArray()
        );
        $this->axisColumns = [];

        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType = $attribute['type'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($attributeTypeConf && $attributeTypeConf['column']) {
                if (in_array($attributeCode, $axisCodes)) {
                    $columnConfig = $attributeTypeConf['column'];
                    $columnConfig = $columnConfig + [
                        'label' => $attribute['label'],
                    ];
                    $this->axisColumns[$attributeCode] = $columnConfig;
                }
            }
        }
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
            + $this->identifierColumn + $this->axisColumns + $this->propertiesColumns;
    }
}
