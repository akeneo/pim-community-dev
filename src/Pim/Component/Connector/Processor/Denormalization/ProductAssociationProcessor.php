<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product association import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var FilterInterface */
    protected $productAssocFilter;

    /** @var bool */
    protected $enabledComparison = true;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository         product repository
     * @param ObjectUpdaterInterface                $updater            product updater
     * @param ValidatorInterface                    $validator          validator of the object
     * @param FilterInterface                       $productAssocFilter product association filter
     * @param ObjectDetacherInterface               $detacher           detacher to remove it from UOW when skip
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        FilterInterface $productAssocFilter,
        ObjectDetacherInterface $detacher
    ) {
        parent::__construct($repository);

        $this->repository = $repository;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->productAssocFilter = $productAssocFilter;
        $this->detacher = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $item = array_merge(
            ['associations' => []],
            $item
        );

        if (!isset($item['identifier'])) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $product = $this->findProduct($item['identifier']);

        if (!$product) {
            $this->skipItemWithMessage($item, sprintf('No product with identifier "%s" has been found', $item['identifier']));
        }

        $parameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $parameters->get('enabledComparison');
        if ($enabledComparison) {
            $item = $this->filterIdenticalData($product, $item);

            if (empty($item)) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        } elseif (!$this->hasImportedAssociations($item)) {
            $this->detachProduct($product);
            $this->stepExecution->incrementSummaryInfo('product_skipped_no_associations');

            return null;
        }

        try {
            $this->updateProduct($product, $item);
        } catch (PropertyException $exception) {
            $this->detachProduct($product);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateProductAssociations($product);
        if ($violations && $violations->count() > 0) {
            $this->detachProduct($product);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     *
     * @return array
     */
    protected function filterIdenticalData(ProductInterface $product, array $item)
    {
        return $this->productAssocFilter->filter($product, $item);
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     *
     * @throws PropertyException
     */
    protected function updateProduct(ProductInterface $product, array $item)
    {
        $this->updater->update($product, $item);
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findProduct($identifier)
    {
        return $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * @param ProductInterface $product
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface|null
     */
    protected function validateProductAssociations(ProductInterface $product)
    {
        $associations = $product->getAssociations();
        foreach ($associations as $association) {
            $violations = $this->validator->validate($association);
            if ($violations->count() > 0) {
                return $violations;
            }
        }

        return null;
    }

    /**
     * Detaches the product from the unit of work is the responsibility of the writer but in this case we
     * want ensure that an updated and invalid product will not be used in the association processor.
     * Also we don't want to keep skipped products in memory
     *
     * @param ProductInterface $product
     */
    protected function detachProduct(ProductInterface $product)
    {
        $this->detacher->detach($product);
    }

    /**
     * It there association(s) in new values ?
     *
     * @param array $item
     *
     * @return bool
     */
    protected function hasImportedAssociations(array $item)
    {
        if (!isset($item['associations'])) {
            return false;
        }

        foreach ($item['associations'] as $association) {
            if (!empty($association['products']) || !empty($association['groups'])) {
                return true;
            }
        }

        return false;
    }
}
