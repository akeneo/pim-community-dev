<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Product import processor, allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends TransformerProcessor
{
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
     * Set whether or not the created product should be activated or not
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Whether or not the created product should be activated or not
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
            'enabled' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.import.enabled.label',
                    'help'  => 'pim_base_connector.import.enabled.help'
                )
            ),
            'categoriesColumn' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.categoriesColumn.label',
                    'help'  => 'pim_base_connector.import.categoriesColumn.help'
                )
            ),
            'familyColumn' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.familyColumn.label',
                    'help'  => 'pim_base_connector.import.familyColumn.help'
                )
            ),
            'groupsColumn' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.groupsColumn.label',
                    'help'  => 'pim_base_connector.import.groupsColumn.help'
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function transform($item)
    {
        return $this->transformer->transform($this->class, $item, array('enabled' => $this->enabled));
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return array(
            $this->familyColumn     => 'family',
            $this->categoriesColumn => 'categories',
            $this->groupsColumn     => 'groups'
        ) + $this->mapping;
    }
}
