<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;

/**
 * Variant group writer, also copy variant group values to belonging products, receive group one per one (cf job
 * configuration) to avoid to hydrate all products related to all groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5, please use to \Pim\Component\Connector\Writer\Doctrine\VariantGroupWriter
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
    protected $productTplApplier;

    /** @var bool */
    protected $copyValues = true;

    /**
     * @param SaverInterface                  $groupSaver
     * @param CacheClearer                    $cacheClearer
     * @param ProductTemplateApplierInterface $productTplApplier
     */
    public function __construct(
        SaverInterface $groupSaver,
        CacheClearer $cacheClearer,
        ProductTemplateApplierInterface $productTplApplier
    ) {
        $this->groupSaver        = $groupSaver;
        $this->cacheClearer      = $cacheClearer;
        $this->productTplApplier = $productTplApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $variantGroups)
    {
        foreach ($variantGroups as $variantGroup) {
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
        return [
            'copyValues' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.copyValuesToProducts.label',
                    'help'  => 'pim_base_connector.import.copyValuesToProducts.help'
                ]
            ]
        ];
    }

    /**
     * Set copy values on products behavior
     *
     * @param bool $apply
     */
    public function setCopyValues($apply)
    {
        $this->copyValues = $apply;
    }

    /**
     * Is copy values on products
     *
     * @return bool
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
        $this->incrementUpdatedVariantGroupCount($variantGroup);
        $this->groupSaver->save($variantGroup);
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
        if ($template && count($template->getValuesData()) > 0 && count($products) > 0) {
            $skippedMessages = $this->productTplApplier->apply($template, $products->toArray());
            $nbSkipped       = count($skippedMessages);
            $nbUpdated       = count($products) - $nbSkipped;
            $this->incrementUpdatedProductsCount($nbUpdated);
            if ($nbSkipped > 0) {
                $this->incrementSkippedProductsCount($nbSkipped, $skippedMessages);
            }
        }
    }

    /**
     * @param GroupInterface $group
     */
    protected function incrementUpdatedVariantGroupCount(GroupInterface $group)
    {
        if (null === $group->getId()) {
            $this->stepExecution->incrementSummaryInfo('create');
        } else {
            $this->stepExecution->incrementSummaryInfo('process');
        }
    }

    /**
     * @param int $nbProducts
     */
    protected function incrementUpdatedProductsCount($nbProducts)
    {
        $this->stepExecution->incrementSummaryInfo('update_products', $nbProducts);
    }

    /**
     * @param int   $nbSkippedProducts
     * @param array $skippedMessages
     */
    protected function incrementSkippedProductsCount($nbSkippedProducts, $skippedMessages)
    {
        $this->stepExecution->incrementSummaryInfo('skip_products', $nbSkippedProducts);

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
