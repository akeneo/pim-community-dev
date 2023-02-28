<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\CleanLineBreaksInTextAttributes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\NonBlockingWarningAggregatorInterface;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Product import processor, allows to,
 *  - create / update
 *  - convert localized attributes
 *  - validate
 *  - skip invalid ones and detach it
 *  - return the valid ones
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface, NonBlockingWarningAggregatorInterface
{
    /** @var Warning[] */
    private array $nonBlockingWarnings = [];

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        private FindProductToImport $findProductToImport,
        private AddParent $addParent,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected ObjectDetacherInterface $detacher,
        protected FilterInterface $productFilter,
        private AttributeFilterInterface $productAttributeFilter,
        private MediaStorer $mediaStorer,
        private RemoveParentInterface $removeParent,
        private CleanLineBreaksInTextAttributes $cleanLineBreaksInTextAttributes
    ) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $itemHasStatus = isset($item['enabled']);
        if (!isset($item['enabled'])) {
            $item['enabled'] = $this->stepExecution->getJobParameters()->get('enabled');
        }

        $identifier = $this->getIdentifier($item);
        $uuid = $this->getUuid($item);
        if (null !== $uuid && !Uuid::isValid($uuid)) {
            $this->skipItemWithMessage($item, 'The uuid must be valid');
        }

        $parentProductModelCode = $item['parent'] ?? '';
        $jobParameters = $this->stepExecution->getJobParameters();
        $convertVariantToSimple = $jobParameters->get('convertVariantToSimple');
        if (true !== $convertVariantToSimple && '' === $parentProductModelCode) {
            unset($item['parent']);
        }

        try {
            $familyCode = $this->getFamilyCode($item);
            $item['family'] = $familyCode;

            $item = $this->productAttributeFilter->filter($item);
            $filteredItem = $this->filterItemData($item);

            $product = $this->findProductToImport->fromFlatData($identifier, $familyCode, $uuid);
        } catch (AccessDeniedException $e) {
            throw $this->skipItemAndReturnException($item, $e->getMessage(), $e);
        }

        if (false === $itemHasStatus && null !== $product->getCreated()) {
            unset($filteredItem['enabled']);
        }

        $enabledComparison = $jobParameters->get('enabledComparison');
        if ($enabledComparison) {
            $filteredItem = $this->filterIdenticalData($product, $filteredItem);

            if (empty($filteredItem) && null !== $product->getCreated()) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        }

        if ($convertVariantToSimple && $product->isVariant() && '' === $filteredItem['parent'] ?? null) {
            try {
                $this->removeParent->from($product);
            } catch (InvalidArgumentException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        if ('' !== $parentProductModelCode && !$product->isVariant()) {
            try {
                $product = $this->addParent->to($product, $parentProductModelCode);
            } catch (\InvalidArgumentException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        if (isset($filteredItem['values'])) {
            try {
                $filteredItem['values'] = $this->mediaStorer->store($filteredItem['values']);
            } catch (InvalidPropertyException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        $cleanedFilteredItem = $this->cleanLineBreaksInTextAttributes->cleanStandardFormat($filteredItem);
        if (is_array($filteredItem['values'] ?? null) && is_array($cleanedFilteredItem['values'] ?? null)) {
            foreach ($cleanedFilteredItem['values'] as $field => $values) {
                if ($values !== $filteredItem['values'][$field]) {
                    $this->nonBlockingWarnings[] = new Warning(
                        $this->stepExecution,
                        'The value for the "%attribute_code%" attribute contains at least one line break. It or they have been replaced by a space during the import.',
                        ['%attribute_code%' => $field],
                        $item
                    );
                }
            }
        }

        try {
            $this->updateProduct($product, $cleanedFilteredItem);
        } catch (PropertyException $exception) {
            $this->detachProduct($product);
            $message = sprintf('%s: %s', $exception->getPropertyName(), $exception->getMessage());
            $this->skipItemWithMessage($item, $message, $exception);
        } catch (InvalidArgumentException | AccessDeniedException $exception) {
            $this->detachProduct($product);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateProduct($product);

        if ($violations->count() > 0) {
            $this->detachProduct($product);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $filteredItem
     *
     * @return array
     */
    protected function filterIdenticalData(ProductInterface $product, array $filteredItem)
    {
        return $this->productFilter->filter($product, $filteredItem);
    }

    protected function getIdentifier(array $item): ?string
    {
        $identifier = $item['identifier'] ?? null;

        return ('' !== $identifier) ? $identifier : null;
    }

    protected function getUuid(array $item): ?string
    {
        $uuid = $item['uuid'] ?? null;

        return ('' !== $uuid) ? $uuid : null;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function getFamilyCode(array $item): string
    {
        if (\array_key_exists('family', $item)) {
            return $item['family'];
        }

        $product = null;
        if (\array_key_exists('uuid', $item) && $item['uuid']) {
            Assert::methodExists($this->repository, 'findOneByUuid');
            $product = $this->repository->findOneByUuid(Uuid::fromString($item['uuid']));
        } elseif (\array_key_exists('identifier', $item) && $item['identifier']) {
            $product = $this->repository->findOneByIdentifier($item['identifier']);
        }
        if (null === $product) {
            return '';
        }

        $family = $product->getFamily();
        if (null === $family) {
            return '';
        }

        return $family->getCode();
    }

    /**
     * Filters item data to remove associations which are imported through a dedicated processor because we need to
     * create any products before to associate them
     *
     * @param array $item
     *
     * @return array
     */
    protected function filterItemData(array $item)
    {
        // After the item will go through a comparator on its fields and values
        // uuid is not part of the needed compared values, so we unset it here
        unset($item['uuid']);
        unset($item['identifier']);

        unset($item['associations']);
        unset($item['quantified_associations']);

        return $item;
    }

    /**
     * @param ProductInterface $product
     * @param array            $filteredItem
     *
     * @throws PropertyException
     */
    protected function updateProduct(ProductInterface $product, array $filteredItem)
    {
        $this->updater->update($product, $filteredItem);
    }

    /**
     * @param ProductInterface $product
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateProduct(ProductInterface $product)
    {
        return $this->validator->validate($product);
    }

    /**
     * Detaches the product from the unit of work is the responsibility of the writer but in this case we
     * want ensure that an updated and invalid product will not be used in the association processor
     *
     * @param ProductInterface $product
     */
    protected function detachProduct(ProductInterface $product)
    {
        $this->detacher->detach($product);
    }

    private function skipItemAndReturnException(array $item, $message, \Exception $previousException = null): InvalidItemException
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }
        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;
        $invalidItem = new FileInvalidItem($item, $itemPosition);

        return new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }

    /**
     * {@inheritDoc}
     */
    public function flushNonBlockingWarnings(): array
    {
        $nonBlockingWarnings = $this->nonBlockingWarnings;
        $this->nonBlockingWarnings = [];

        return $nonBlockingWarnings;
    }
}
