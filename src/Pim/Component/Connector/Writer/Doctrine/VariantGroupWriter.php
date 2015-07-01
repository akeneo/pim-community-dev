<?php

namespace Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;

/**
 * Variant group writer, also copy variant group values to belonging products, receive group one per one (cf job
 * configuration) to avoid to hydrate all products related to all groups
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter extends BaseWriter
{
    /** @var ProductTemplateApplierInterface */
    protected $productTplApplier;

    /** @var bool */
    protected $copyValues = true;

    /**
     * @param BulkSaverInterface              $groupSaver
     * @param BulkObjectDetacherInterface     $detacher
     * @param ProductTemplateApplierInterface $productTplApplier
     */
    public function __construct(
        BulkSaverInterface $groupSaver,
        BulkObjectDetacherInterface $detacher,
        ProductTemplateApplierInterface $productTplApplier
    ) {
        parent::__construct($groupSaver, $detacher);

        $this->productTplApplier = $productTplApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $variantGroups)
    {
        if ($this->isCopyValues()) {
            $this->copyValuesToProducts($variantGroups);
        }

        parent::write($variantGroups);
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
                    'label' => 'pim_connector.import.copyValuesToProducts.label',
                    'help'  => 'pim_connector.import.copyValuesToProducts.help'
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
     * Copy variant group values to products
     *
     * @param array $variantGroups
     */
    protected function copyValuesToProducts(array $variantGroups)
    {
        foreach ($variantGroups as $variantGroup) {
            $template = $variantGroup->getProductTemplate();
            $products = $variantGroup->getProducts();
            if ($template && count($template->getValuesData()) > 0 && count($products) > 0) {
                $skippedMessages = $this->productTplApplier->apply($template, $products->toArray());
                $nbSkipped = count($skippedMessages);
                $nbUpdated = count($products) - $nbSkipped;
                $this->incrementUpdatedProductsCount($nbUpdated);
                if ($nbSkipped > 0) {
                    $this->incrementSkippedProductsCount($skippedMessages, $nbSkipped);
                }
            }
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
     * @param array $skippedMessages
     * @param int   $nbSkippedProducts
     */
    protected function incrementSkippedProductsCount(array $skippedMessages, $nbSkippedProducts)
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
