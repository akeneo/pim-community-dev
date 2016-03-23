<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product draft import processor, allows to,
 *  - update
 *  - validate
 *  - skip invalid ones
 *  - return the valid ones
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftProcessor extends AbstractProcessor
{
    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProductDraftBuilderInterface */
    protected $productDraftBuilder;

    /** @var ProductDraftApplierInterface */
    protected $productDraftApplier;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter      array converter
     * @param IdentifiableObjectRepositoryInterface $repository          product repository
     * @param ObjectUpdaterInterface                $updater             product updater
     * @param ValidatorInterface                    $validator           product validator
     * @param ProductDraftBuilderInterface          $productDraftBuilder product draft builder
     * @param ProductDraftApplierInterface          $productDraftApplier product draft applier
     * @param ProductDraftRepositoryInterface       $productDraftRepo    product draft repository
     * @param AttributeConverterInterface           $localizedConverter  attributes localized converter
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductDraftBuilderInterface $productDraftBuilder,
        ProductDraftApplierInterface $productDraftApplier,
        ProductDraftRepositoryInterface $productDraftRepo,
        AttributeConverterInterface $localizedConverter
    ) {
        parent::__construct($repository);

        $this->arrayConverter      = $arrayConverter;
        $this->updater             = $updater;
        $this->validator           = $validator;
        $this->productDraftBuilder = $productDraftBuilder;
        $this->productDraftApplier = $productDraftApplier;
        $this->productDraftRepo    = $productDraftRepo;
        $this->localizedConverter  = $localizedConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);

        $convertedItem = $this->convertLocalizedAttributes($convertedItem);
        $violations    = $this->localizedConverter->getViolations();

        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $identifier = $this->getIdentifier($convertedItem);

        $product = $this->findProduct($identifier);
        if (!$product) {
            $this->skipItemWithMessage($item, sprintf('Product "%s" does not exist', $identifier));
        }

        $product = $this->applyDraftToProduct($product);

        try {
            $this->updateProduct($product, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateProduct($product);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $this->buildDraft($product, $item);
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
     * Checks and converts localized attributes to default format
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
     * Apply current draft values to product to fix problem with optional attributes
     *
     * @param ProductInterface $product
     *
     * @return ProductInterface
     */
    protected function applyDraftToProduct(ProductInterface $product)
    {
        $productDraft = $this->getProductDraft($product);

        if (null !== $productDraft) {
            $this->productDraftApplier->applyAllChanges($product, $productDraft);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     *
     * @return ProductDraft|null
     */
    protected function getProductDraft(ProductInterface $product)
    {
        return $this->productDraftRepo->findUserProductDraft($product, $this->getCodeInstance());
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties();
        if (!isset($convertedItem[$identifierProperty[0]])) {
            throw new \InvalidArgumentException(
                sprintf('Identifier property "%s" is expected', $identifierProperty[0])
            );
        }

        return $convertedItem[$identifierProperty[0]][0]['data'];
    }

    /**
     * @param string $identifier
     *
     * @throws \RuntimeException
     *
     * @return ProductInterface|null
     */
    protected function findProduct($identifier)
    {
        return $this->repository->findOneByIdentifier($identifier);
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
     * Build product draft. If there is:
     *  - diff between product and draft: return new draft, it will be created in writer
     *  - no diff between product and draft and there is no draft for this product in DB: skip draft
     *  - no diff between product and draft and there is a draft for this product in DB: return old draft, it will be
     *      deleted in writer
     *
     * @param ProductInterface $product
     * @param array            $item
     *
     * @throws InvalidItemException
     *
     * @return ProductDraft|null
     */
    protected function buildDraft(ProductInterface $product, array $item)
    {
        $productDraft = $this->productDraftBuilder->build($product, $this->getCodeInstance());

        // no draft has been created because there is no diff between proposal and product
        if (null === $productDraft) {
            $deprecatedDraft = $this->getProductDraft($product);
            if (null !== $deprecatedDraft) {
                $deprecatedDraft->setChanges([]);

                return $deprecatedDraft;
            }

            $this->stepExecution->incrementSummaryInfo('proposal_skipped');

            return null;
        }

        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_TO_REVIEW);

        return $productDraft;
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
     * @return string
     */
    protected function getCodeInstance()
    {
        return $this->stepExecution->getJobExecution()->getJobInstance()->getCode();
    }
}
