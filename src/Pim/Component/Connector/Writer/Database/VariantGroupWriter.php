<?php

namespace Pim\Component\Connector\Writer\Database;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Manager\ProductTemplateApplierInterface;
use Pim\Component\Catalog\Model\GroupInterface;

/**
 * Variant group writer, also copy variant group values to belonging products, receive group one per one (cf job
 * configuration) to avoid to hydrate all products related to all groups
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $bulkSaver;

    /** @var BulkObjectDetacherInterface */
    protected $bulkDetacher;

    /** @var ProductTemplateApplierInterface */
    protected $productTplApplier;

    /**
     * @param BulkSaverInterface              $bulkSaver
     * @param BulkObjectDetacherInterface     $bulkDetacher
     * @param ProductTemplateApplierInterface $productTplApplier
     */
    public function __construct(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        ProductTemplateApplierInterface $productTplApplier
    ) {
        $this->bulkSaver = $bulkSaver;
        $this->bulkDetacher = $bulkDetacher;
        $this->productTplApplier = $productTplApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $variantGroups)
    {
        $this->incrementCount($variantGroups);
        $this->bulkSaver->saveAll($variantGroups);

        $jobParameters = $this->stepExecution->getJobParameters();
        $isCopyValues = $jobParameters->get('copyValues');
        if ($isCopyValues) {
            $this->copyValuesToProducts($variantGroups);
        }

        $this->bulkDetacher->detachAll($variantGroups);
    }

    /**
     * Copy variant group values to products
     *
     * @param GroupInterface[] $variantGroups
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
                $this->bulkDetacher->detachAll($products->toArray());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
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
                sprintf('Copy of values to product "%s" skipped.', $productIdentifier),
                [],
                new DataInvalidItem($messages)
            );
        }
    }

    /**
     * @param array $objects
     */
    protected function incrementCount(array $objects)
    {
        foreach ($objects as $object) {
            if ($object->getId()) {
                $this->stepExecution->incrementSummaryInfo('process');
            } else {
                $this->stepExecution->incrementSummaryInfo('create');
            }
        }
    }
}
