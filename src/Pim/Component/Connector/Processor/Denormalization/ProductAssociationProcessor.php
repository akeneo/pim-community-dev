<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product association import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var ProductFilterInterface */
    protected $productAssocFilter;

    /** @var bool */
    protected $enabledComparison = true;

    /**
     * @param ArrayConverterInterface               $arrayConverter     array converter
     * @param IdentifiableObjectRepositoryInterface $repository         product repository
     * @param ObjectUpdaterInterface                $updater            product updater
     * @param ValidatorInterface                    $validator          validator of the object
     * @param ProductFilterInterface                $productAssocFilter product association filter
     * @param ObjectDetacherInterface               $detacher           detacher to remove it from UOW when skip
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductFilterInterface $productAssocFilter,
        ObjectDetacherInterface $detacher
    ) {
        parent::__construct($repository);

        $this->arrayConverter     = $arrayConverter;
        $this->repository         = $repository;
        $this->updater            = $updater;
        $this->validator          = $validator;
        $this->productAssocFilter = $productAssocFilter;
        $this->detacher           = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $identifier    = $this->getIdentifier($convertedItem);

        if (null === $identifier) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $product = $this->findProduct($identifier);

        if (!$product) {
            $this->skipItemWithMessage($item, sprintf('No product with identifier "%s" has been found', $identifier));
        }

        $parameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $parameters->get('enabledComparison');
        if ($enabledComparison) {
            $convertedItem = $this->filterIdenticalData($product, $convertedItem);

            if (empty($convertedItem)) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        }

        try {
            $this->updateProduct($product, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
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
     * @param array            $convertedItem
     *
     * @return array
     */
    protected function filterIdenticalData(ProductInterface $product, array $convertedItem)
    {
        return $this->productAssocFilter->filter($product, $convertedItem);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        $convertedItem = $this->arrayConverter->convert($item, ['with_associations' => true]);
        $convertedItem = array_merge(
            ['associations' => []],
            $convertedItem
        );

        return $convertedItem;
    }

    /**
     * @param ProductInterface $product
     * @param array            $convertedItem
     *
     * @throws \InvalidArgumentException
     */
    protected function updateProduct(ProductInterface $product, array $convertedItem)
    {
        $this->updater->update($product, $convertedItem);
    }

    /**
     * @param array $convertedItem
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties();

        return $convertedItem[$identifierProperty[0]][0]['data'];
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
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface|null
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
}
