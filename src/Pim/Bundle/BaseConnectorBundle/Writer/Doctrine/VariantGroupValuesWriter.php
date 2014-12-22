<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Component\Resource\Model\SaverInterface;

/**
 * Variant group writer, also copy variant group values to belonging products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupValuesWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var CacheClearer */
    protected $cacheClearer;

    /** @var boolean */
    protected $copyValuesOnProducts = true;

    /**
     * @param SaverInterface $groupSaver
     * @param CacheClearer   $cacheClearer
     */
    public function __construct(SaverInterface $groupSaver, CacheClearer $cacheClearer)
    {
        $this->groupSaver   = $groupSaver; // TODO could use bulk but can have issue when save products too
        $this->cacheClearer = $cacheClearer; // TODO : useful ?
        // TODO : versioning of products will be true by default ...
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->groupSaver->save($item, ['copy_values_to_products' => true]);
            $this->incrementCount($item);
            // TODO : how to know skip if no direct call to the template manager ?
        }

        $this->cacheClearer->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'copyValuesOnProducts' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.import.copyValuesOnProducts.label',
                    'help'  => 'pim_base_connector.import.copyValuesOnProducts.help'
                )
            )
        );
    }

    /**
     * Set copy values on products behavior
     *
     * @param boolean $apply
     */
    public function setCopyValuesOnProducts($apply)
    {
        $this->copyValuesOnProducts = $apply;
    }

    /**
     * Is copy values on products
     *
     * @return boolean
     */
    public function isCopyValuesOnProducts()
    {
        return $this->copyValuesOnProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param GroupInterface $group
     */
    protected function incrementCount(GroupInterface $group)
    {
        $this->stepExecution->incrementSummaryInfo('update');
        if ($this->copyValuesOnProducts) {
            // TODO : add a method in batch bundle
            $summary = $this->stepExecution->getSummary();
            $previousAmount = isset($summary['update_products']) ? $summary['update_products'] : 0;
            $previousAmount = is_numeric($previousAmount) ? $previousAmount : 0;
            $total = $previousAmount + count($group->getProducts());
            $this->stepExecution->addSummaryInfo('update_products', $total);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->cacheClearer->clear(true);
    }
}
