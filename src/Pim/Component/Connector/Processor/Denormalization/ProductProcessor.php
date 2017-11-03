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
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\AddParent;
use Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
class ProductProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var FindProductToImport */
    private $productToImport;

    /** @var AddParent */
    private $addParent;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var FilterInterface */
    protected $productFilter;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param FindProductToImport                   $productToImport
     * @param AddParent                             $addParent
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $detacher
     * @param FilterInterface                       $productFilter
     * @param AttributeFilterInterface              $productAttributeFilter
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        FindProductToImport $productToImport,
        AddParent $addParent,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        FilterInterface $productFilter,
        AttributeFilterInterface $productAttributeFilter
    ) {
        parent::__construct($repository);

        $this->productToImport = $productToImport;
        $this->addParent = $addParent;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->detacher = $detacher;
        $this->productFilter = $productFilter;
        $this->productAttributeFilter = $productAttributeFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $itemHasStatus = isset($item['enabled']);
        if (!isset($item['enabled'])) {
            $item['enabled'] = $jobParameters = $this->stepExecution->getJobParameters()->get('enabled');
        }

        $identifier = $this->getIdentifier($item);

        if (null === $identifier) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $parentProductModelCode = $item['parent'] ?? '';

        try {
            $item = $this->productAttributeFilter->filter($item);

            $familyCode = $this->getFamilyCode($item);
            $filteredItem = $this->filterItemData($item);

            $product = $this->productToImport->fromFlatData($identifier, $familyCode, $parentProductModelCode);
        } catch (AccessDeniedException $e) {
            $this->skipItemWithMessage($item, $e->getMessage(), $e);
        }

        if (false === $itemHasStatus && null !== $product->getId()) {
            unset($filteredItem['enabled']);
        }

        $jobParameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $jobParameters->get('enabledComparison');
        if ($enabledComparison) {
            $filteredItem = $this->filterIdenticalData($product, $filteredItem);

            if (empty($filteredItem) && null !== $product->getId()) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        }

        $product = $this->addParent->to($product, $parentProductModelCode);

        try {
            $this->updateProduct($product, $filteredItem);
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

    /**
     * @param array $item
     *
     * @return string|null
     */
    protected function getIdentifier(array $item)
    {
        return isset($item['identifier']) ? $item['identifier'] : null;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function getFamilyCode(array $item): string
    {
        return $item['family'] ?? '';
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
        foreach ($this->repository->getIdentifierProperties() as $identifierProperty) {
            unset($item['values'][$identifierProperty]);
        }
        unset($item['identifier']);
        unset($item['associations']);

        return $item;
    }

    /**
     * @param string      $identifier
     * @param string|null $familyCode
     * @param string      $parentCode
     *
     * @return ProductInterface
     * @throws AccessDeniedException
     */
    protected function findOrCreateProduct(
        string $identifier,
        ?string $familyCode,
        string $parentCode
    ): ProductInterface {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (null === $product && '' !== $parentCode) {
            $product = $this->variantProductBuilder->createProduct($identifier, $familyCode);
        }

        if (null === $product) {
            $product = $this->productBuilder->createProduct($identifier, $familyCode);
        }

        return $product;
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
}
