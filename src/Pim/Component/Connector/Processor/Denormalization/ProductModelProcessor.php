<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Connector\Processor\Denormalization\AttributeFilter\AttributeFilterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product model import processor, allows to,
 *  - create / update
 *  - convert localized attributes
 *  - validate
 *  - skip invalid ones and detach it
 *  - return the valid ones
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private const SUB_PRODUCT_MODEL = 'sub_product_model';
    private const ROOT_PRODUCT_MODEL = 'root_product_model';

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var FilterInterface */
    private $productModelFilter;

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /** @var AttributeFilterInterface */
    private $productModelAttributeFilter;

    /** @var string */
    private $importType;

    /**
     * @param SimpleFactoryInterface                $productModelFactory
     * @param ObjectUpdaterInterface                $productModelUpdater
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param ValidatorInterface                    $validator
     * @param FilterInterface                       $productModelFilter
     * @param ObjectDetacherInterface               $objectDetacher
     * @param AttributeFilterInterface              $productModelAttributeFilter
     * @param string                                $importType
     */
    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ValidatorInterface $validator,
        FilterInterface $productModelFilter,
        ObjectDetacherInterface $objectDetacher,
        AttributeFilterInterface $productModelAttributeFilter,
        string $importType
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelRepository = $productModelRepository;
        $this->validator = $validator;
        $this->productModelFilter = $productModelFilter;
        $this->objectDetacher = $objectDetacher;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->importType = $importType;
    }

    /**
     * {@inheritdoc}
     */
    public function process($flatProductModel): ?ProductModelInterface
    {
        $parent = $flatProductModel['parent'] ?? '';
        if ($this->importType === self::ROOT_PRODUCT_MODEL && !empty($parent) ||
            $this->importType === self::SUB_PRODUCT_MODEL && empty($parent)
        ) {
            $this->stepExecution->incrementSummaryInfo(sprintf('skipped_in_%s', $this->importType));

            return null;
        }

        if (!isset($flatProductModel['code'])) {
            $this->skipItemWithMessage($flatProductModel, 'The code must be filled');
        }

        $flatProductModel = $this->productModelAttributeFilter->filter($flatProductModel);
        $productModel = $this->findOrCreateProductModel($flatProductModel['code']);

        $jobParameters = $this->stepExecution->getJobParameters();
        if ($jobParameters->get('enabledComparison') && null !== $productModel->getId()) {
            // We don't compare immutable fields
            $flatProductModelToCompare = $flatProductModel;
            unset($flatProductModelToCompare['code']);

            $flatProductModel = $this->productModelFilter->filter($productModel, $flatProductModelToCompare);

            if (empty($flatProductModel)) {
                $this->objectDetacher->detach($productModel);
                $this->stepExecution->incrementSummaryInfo('product_model_skipped_no_diff');

                return null;
            }
        }

        try {
            $this->productModelUpdater->update($productModel, $flatProductModel);
        } catch (PropertyException $exception) {
            $this->objectDetacher->detach($productModel);
            $message = sprintf('%s: %s', $exception->getPropertyName(), $exception->getMessage());
            $this->skipItemWithMessage($flatProductModel, $message, $exception);
        }

        $violations = $this->validator->validate($productModel);

        if ($violations->count() > 0) {
            $this->objectDetacher->detach($productModel);
            $this->skipItemWithConstraintViolations($flatProductModel, $violations);
        }

        return $productModel;
    }

    /**
     * @param string $code
     *
     * @return ProductModelInterface
     */
    private function findOrCreateProductModel(string $code): ProductModelInterface
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($code);
        if (null === $productModel) {
            $productModel = $this->productModelFactory->create();
        }

        return $productModel;
    }
}
