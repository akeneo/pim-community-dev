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
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
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
    /** @var StandardArrayConverterInterface */
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
    protected $enabled = true;

    /** @var bool */
    protected $itemHasStatus = false;

    /** @var string */
    protected $categoriesColumn = 'categories';

    /** @var string */
    protected $familyColumn  = 'family';

    /** @var string */
    protected $groupsColumn  = 'groups';

    /** @var bool */
    protected $enabledComparison = true;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var ProductFilterInterface */
    protected $productFilter;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter     array converter
     * @param IdentifiableObjectRepositoryInterface $repository         product repository
     * @param ProductBuilderInterface               $builder            product builder
     * @param ObjectUpdaterInterface                $updater            product updater
     * @param ValidatorInterface                    $validator          product validator
     * @param ObjectDetacherInterface               $detacher           detacher to remove it from UOW when skip
     * @param ProductFilterInterface                $productFilter      product filter
     * @param AttributeConverterInterface           $localizedConverter attributes localized converter
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
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

        if ($this->enabledComparison) {
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
     * Set whether or not the created product should be activated or not
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Whether or not the created product should be activated or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the categories column
     *
     * @param string $categoriesColumn
     */
    public function setCategoriesColumn($categoriesColumn)
    {
        $this->categoriesColumn = $categoriesColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getCategoriesColumn()
    {
        return $this->categoriesColumn;
    }

    /**
     * Set the groups column
     *
     * @param string $groupsColumn
     */
    public function setGroupsColumn($groupsColumn)
    {
        $this->groupsColumn = $groupsColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getGroupsColumn()
    {
        return $this->groupsColumn;
    }

    /**
     * Set the family column
     *
     * @param string $familyColumn
     */
    public function setFamilyColumn($familyColumn)
    {
        $this->familyColumn = $familyColumn;
    }

    /**
     * Get the family column
     *
     * @return string
     */
    public function getFamilyColumn()
    {
        return $this->familyColumn;
    }

    /**
     * Set whether or not the comparison between original values and imported values should be activated
     *
     * @param bool $enabledComparison
     */
    public function setEnabledComparison($enabledComparison)
    {
        $this->enabledComparison = $enabledComparison;
    }

    /**
     * Whether or not the comparison between original values and imported values is activated
     *
     * @return bool
     */
    public function isEnabledComparison()
    {
        return $this->enabledComparison;
    }

    /**
     * Set the separator for decimal
     *
     * @param string $decimalSeparator
     */
    public function setDecimalSeparator($decimalSeparator)
    {
        $this->decimalSeparator = $decimalSeparator;
    }

    /**
     * Get the delimiter for decimal
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * Set the format for date field
     *
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Get the format for the date field
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'enabled' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.enabled.label',
                    'help'  => 'pim_connector.import.enabled.help'
                ]
            ],
            'categoriesColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.categoriesColumn.label',
                    'help'  => 'pim_connector.import.categoriesColumn.help'
                ]
            ],
            'familyColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.familyColumn.label',
                    'help'  => 'pim_connector.import.familyColumn.help'
                ]
            ],
            'groupsColumn' => [
                'options' => [
                    'label' => 'pim_connector.import.groupsColumn.label',
                    'help'  => 'pim_connector.import.groupsColumn.help'
                ]
            ],
            'enabledComparison' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.enabledComparison.label',
                    'help'  => 'pim_connector.import.enabledComparison.help'
                ]
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'label' => 'pim_connector.import.decimalSeparator.label',
                    'help'  => 'pim_connector.import.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'label' => 'pim_connector.import.dateFormat.label',
                    'help'  => 'pim_connector.import.dateFormat.help'
                ]
            ]
        ];
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
        return $this->localizedConverter->convertToDefaultFormats($convertedItem, [
            'decimal_separator' => $this->decimalSeparator,
            'date_format'       => $this->dateFormat
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
        $identifierProperty = $this->repository->getIdentifierProperties();
        if (!isset($convertedItem[$identifierProperty[0]])) {
            throw new \RuntimeException(sprintf('Identifier property "%s" is expected', $identifierProperty[0]));
        }

        return $convertedItem[$identifierProperty[0]][0]['data'];
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
     * want to ensure that an updated and invalid product will not be used in the association processor
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
        return [
            $this->familyColumn     => 'family',
            $this->categoriesColumn => 'categories',
            $this->groupsColumn     => 'groups'
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultValues()
    {
        return ['enabled' => $this->enabled];
    }
}
