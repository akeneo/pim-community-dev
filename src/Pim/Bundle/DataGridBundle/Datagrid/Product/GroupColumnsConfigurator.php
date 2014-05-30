<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;

/**
 * Columns configurator for products grid (used to associate products to groups)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupColumnsConfigurator extends ColumnsConfigurator
{
    /** @var GroupRepository */
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
     * @param ConfigurationRegistry $registry
     * @param RequestParameters     $requestParams
     * @param GroupRepository       $groupRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        RequestParameters $requestParams,
        GroupRepository $groupRepository
    ) {
        parent::__construct($registry);
        $this->requestParams   = $requestParams;
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
     * @return Group
     */
    protected function getGroup()
    {
        $groupId = $this->request->get('id', null);
        if (!$groupId) {
            $groupId = $this->requestParams->get('currentGroup', null);
        }

        $group = $this->groupRepository->findOne($groupId);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAxisColumns()
    {

        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $axisCodes = array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $this->getGroup()->getAttributes()->toArray()
        );
        $this->axisColumns = array();

        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($attributeTypeConf && $attributeTypeConf['column']) {
                if (in_array($attributeCode, $axisCodes)) {
                    $columnConfig = $attributeTypeConf['column'];
                    $columnConfig = $columnConfig + array(
                        'label' => $attribute['label'],
                    );
                    $this->axisColumns[$attributeCode] = $columnConfig;
                }
            }
        }
    }

    /**
     * Sort the columns
     *
     * @return null
     */
    protected function sortColumns()
    {
        $this->displayedColumns = $this->editableColumns + $this->primaryColumns + $this->identifierColumn
            + $this->axisColumns + $this->propertiesColumns;
    }
}
