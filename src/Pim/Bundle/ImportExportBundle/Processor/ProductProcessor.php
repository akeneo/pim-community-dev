<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer;

/**
 * Product import processor
 * Allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface,
 StepExecutionAwareInterface
{
    /**
     * @var ORMProductTransformer
     */
    protected $transformer;

    /**
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var string
     */
    protected $categoriesColumn = 'categories';

    /**
     * @var string
     */
    protected $familyColumn  = 'family';

    /**
     * @var string
     */
    protected $groupsColumn  = 'groups';

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param ORMProductTransformer $transformer
     */
    public function __construct(ORMProductTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Set wether or not the created product should be activated or not
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Wether or not the created product should be activated or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the categories column
     *
     * @param string $categoriesColumn
     */
    public function setCategoriesColumn($categoriesColumn)
    {
        $this->categoriesColumn = $categoriesColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getCategoriesColumn()
    {
        return $this->categoriesColumn;
    }

    /**
     * Set the groups column
     *
     * @param string $groupsColumn
     */
    public function setGroupsColumn($groupsColumn)
    {
        $this->groupsColumn = $groupsColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getGroupsColumn()
    {
        return $this->groupsColumn;
    }

    /**
     * Set the family column
     *
     * @param string $familyColumn
     */
    public function setFamilyColumn($familyColumn)
    {
        $this->familyColumn = $familyColumn;
    }

    /**
     * Get the family column
     *
     * @return string
     */
    public function getFamilyColumn()
    {
        return $this->familyColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'enabled'             => array(
                'type' => 'switch',
            ),
            'categoriesColumn'    => array(),
            'familyColumn'        => array(),
            'groupsColumn'        => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $this->transformer->getProduct(
            $item,
            array(
                'family'        => $this->familyColumn,
                'categories'    => $this->categoriesColumn,
                'groups'        => $this->groupsColumn
            ),
            array(
                'enabled'       => $this->enabled
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
