<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Akeneo\Component\Persistence\SaverInterface;

/**
 * Variant group writer, also copy variant group values to belonging products, receive group one per one (cf job
 * configuration) to avoid to hydrate all products related to all groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter extends AbstractConfigurableStepElement implements
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
    protected $copyValues = true;

    /**
     * @param SaverInterface                  $groupSaver
     * @param CacheClearer                    $cacheClearer
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
            if ($this->isCopyValues()) {
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
            'copyValues' => array(
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
    public function setCopyValues($apply)
    {
        $this->copyValues = $apply;
    }

    /**
     * Is copy values on products
     *
     * @return boolean
     */
    public function isCopyValues()
    {
        return $this->copyValues;
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
            if ($nbSkipped > 0) {
                $this->incrementSkippedProductsCount($nbSkipped, $skippedMessages);
            }
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
        $summaryKey = 'update_products';
        $this->stepExecution->incrementSummaryInfo($summaryKey, $nbProducts);
    }

    /**
     * @param integer $nbSkippedProducts
     * @param array   $skippedMessages
     */
    protected function incrementSkippedProductsCount($nbSkippedProducts, $skippedMessages)
    {
        $summaryKey = 'skip_products';
        $this->stepExecution->incrementSummaryInfo($summaryKey, $nbSkippedProducts);

        foreach ($skippedMessages as $productIdentifier => $messages) {
            $this->stepExecution->addWarning(
                $this->getName(),
                sprintf('Copy of values to product "%s" skipped.', $productIdentifier),
                [],
                $messages
            );
        }
    }
}
