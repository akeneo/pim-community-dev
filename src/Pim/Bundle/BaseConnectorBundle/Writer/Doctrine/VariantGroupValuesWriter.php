<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Component\Resource\Model\SaverInterface;

/**
 * Variant group writer, also copy variant group values to belonging products, receive group one per one (cf job
 * configuration) to avoid to hydrate all products related to all groups
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

    /** @var ProductTemplateApplierInterface */
    protected $productTemplateApplier;

    /** @var boolean */
    protected $copyValuesToProducts = true;

    /**
     * @param SaverInterface $groupSaver
     * @param CacheClearer $cacheClearer
     * @param ProductTemplateApplierInterface $productTemplateApplier
     */
    public function __construct(
        SaverInterface $groupSaver,
        CacheClearer $cacheClearer,
        ProductTemplateApplierInterface $productTemplateApplier
    ) {
        $this->groupSaver   = $groupSaver;
        $this->cacheClearer = $cacheClearer;
        $this->productTemplateApplier = $productTemplateApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $variantGroup) {
            $this->saveVariantGroup($variantGroup);
            if ($this->copyValuesToProducts) {
                $this->copyValuesToProducts($variantGroup);
            }
        }

        $this->cacheClearer->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'copyValuesToProducts' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.import.copyValuesToProducts.label',
                    'help'  => 'pim_base_connector.import.copyValuesToProducts.help'
                )
            )
        );
    }

    /**
     * Set copy values on products behavior
     *
     * @param boolean $apply
     */
    public function setCopyValuesToProducts($apply)
    {
        $this->copyValuesToProducts = $apply;
    }

    /**
     * Is copy values on products
     *
     * @return boolean
     */
    public function isCopyValuesToProducts()
    {
        return $this->copyValuesToProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Save the variant group and related product template
     *
     * @param GroupInterface $variantGroup
     */
    protected function saveVariantGroup(GroupInterface $variantGroup)
    {
        $this->groupSaver->save($variantGroup);
        $this->incrementUpdatedVariantGroupCount($variantGroup);
    }

    /**
     * Copy variant group values to products
     *
     * @param GroupInterface $variantGroup
     */
    protected function copyValuesToProducts(GroupInterface $variantGroup)
    {
        $template = $variantGroup->getProductTemplate();
        $products = $variantGroup->getProducts();
        if ($template && count($products) > 0) {
            $products = $products->count() > 0 ? $products->toArray() : [];
            $skippedMessages = $this->productTemplateApplier->apply($template, $products);
            $nbSkipped = count($skippedMessages);
            $nbUpdated = count($products) - $nbSkipped;
            $this->incrementUpdatedProductsCount($nbUpdated);
            $this->incrementSkippedProductsCount($nbSkipped, $skippedMessages);
        }
    }

    /**
     *
     */
    protected function incrementUpdatedVariantGroupCount()
    {
        $this->stepExecution->incrementSummaryInfo('update');
    }

    /**
     * @param integer $nbProducts
     */
    protected function incrementUpdatedProductsCount($nbProducts)
    {
        // TODO : update the method StepExecution::incrementSummaryInfo to add an optional $incrementNumber arg
        $summaryKey = 'update_products';
        $summary = $this->stepExecution->getSummary();
        $previousAmount = isset($summary[$summaryKey]) ? $summary[$summaryKey] : 0;
        $previousAmount = is_numeric($previousAmount) ? $previousAmount : 0;
        $total = $previousAmount + $nbProducts;
        $this->stepExecution->addSummaryInfo($summaryKey, $total);
    }

    /**
     * @param integer $nbSkippedProducts
     * @param array   $skippedMessages
     */
    protected function incrementSkippedProductsCount($nbSkippedProducts, $skippedMessages)
    {
        // TODO : update the method StepExecution::incrementSummaryInfo to add an optional $incrementNumber arg
        $summaryKey = 'skip_products';
        $summary = $this->stepExecution->getSummary();
        $previousAmount = isset($summary[$summaryKey]) ? $summary[$summaryKey] : 0;
        $previousAmount = is_numeric($previousAmount) ? $previousAmount : 0;
        $total = $previousAmount + $nbSkippedProducts;
        $this->stepExecution->addSummaryInfo($summaryKey, $total);

        foreach ($skippedMessages as $productIdentifier => $messages) {
            $this->stepExecution->addWarning(
                $this->getName(),
                sprintf('Copy of values to product "%s" skipped.', $productIdentifier),
                [],
                $messages
            );
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
