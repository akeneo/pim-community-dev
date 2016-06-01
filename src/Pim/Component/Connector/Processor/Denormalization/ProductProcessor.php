<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
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
class ProductProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var ProductBuilderInterface */
    protected $builder;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var bool */
    protected $itemHasStatus = false;

    /** @var ProductFilterInterface */
    protected $productFilter;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /**
     * @param ArrayConverterInterface               $arrayConverter     array converter
     * @param IdentifiableObjectRepositoryInterface $repository         product repository
     * @param ProductBuilderInterface               $builder            product builder
     * @param ObjectUpdaterInterface                $updater            product updater
     * @param ValidatorInterface                    $validator          product validator
     * @param ObjectDetacherInterface               $detacher           detacher to remove it from UOW when skip
     * @param ProductFilterInterface                $productFilter      product filter
     * @param AttributeConverterInterface           $localizedConverter attributes localized converter
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ProductBuilderInterface $builder,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ProductFilterInterface $productFilter,
        AttributeConverterInterface $localizedConverter
    ) {
        parent::__construct($repository);

        $this->arrayConverter     = $arrayConverter;
        $this->builder            = $builder;
        $this->updater            = $updater;
        $this->validator          = $validator;
        $this->detacher           = $detacher;
        $this->productFilter      = $productFilter;
        $this->localizedConverter = $localizedConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);

        $convertedItem = $this->convertLocalizedAttributes($convertedItem);
        $violations = $this->localizedConverter->getViolations();

        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $identifier = $this->getIdentifier($convertedItem);

        if (null === $identifier) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $familyCode    = $this->getFamilyCode($convertedItem);
        $filteredItem  = $this->filterItemData($convertedItem);

        $product = $this->findOrCreateProduct($identifier, $familyCode);

        if (false === $this->itemHasStatus && null !== $product->getId()) {
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

        try {
            $this->updateProduct($product, $filteredItem);
        } catch (\InvalidArgumentException $exception) {
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
     * Check and convert localized attributes to default format
     *
     * @param array $convertedItem
     *
     * @return array
     */
    protected function convertLocalizedAttributes(array $convertedItem)
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $this->localizedConverter->convertToDefaultFormats($convertedItem, [
            'decimal_separator' => $jobParameters->get('decimalSeparator'),
            'date_format'       => $jobParameters->get('dateFormat')
        ]);
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
     * @return array
     */
    protected function convertItemData(array $item)
    {
        $this->itemHasStatus = array_key_exists('enabled', $item);

        return $this->arrayConverter->convert($item, $this->getArrayConverterOptions());
    }

    /**
     * @param array $convertedItem
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties()[0];
        if (!isset($convertedItem[$identifierProperty])) {
            throw new \RuntimeException(sprintf('Identifier property "%s" is expected', $identifierProperty));
        }

        return $convertedItem[$identifierProperty][0]['data'];
    }

    /**
     * @param array $convertedItem
     *
     * @return string|null
     */
    protected function getFamilyCode(array $convertedItem)
    {
        return isset($convertedItem['family']) ? $convertedItem['family'] : null;
    }

    /**
     * Filters item data to remove associations which are imported through a dedicated processor because we need to
     * create any products before to associate them
     *
     * @param array $convertedItem
     *
     * @return array
     */
    protected function filterItemData(array $convertedItem)
    {
        unset($convertedItem[$this->repository->getIdentifierProperties()[0]]);
        unset($convertedItem['associations']);

        return $convertedItem;
    }

    /**
     * @param string      $identifier
     * @param string|null $familyCode
     *
     * @return ProductInterface
     */
    protected function findOrCreateProduct($identifier, $familyCode)
    {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (!$product) {
            $product = $this->builder->createProduct($identifier, $familyCode);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $filteredItem
     *
     * @throws \InvalidArgumentException
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

    /**
     * @return array
     */
    protected function getArrayConverterOptions()
    {
        return [
            'mapping'           => $this->getMapping(),
            'default_values'    => $this->getDefaultValues(),
            'with_associations' => false
        ];
    }

    /**
     * @return array
     */
    protected function getMapping()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return [
            $jobParameters->get('familyColumn')     => 'family',
            $jobParameters->get('categoriesColumn') => 'categories',
            $jobParameters->get('groupsColumn')     => 'groups'
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultValues()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return ['enabled' => $jobParameters->get('enabled')];
    }
}
