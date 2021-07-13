<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\CleanLineBreaksInTextAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\NonBlockingWarningAggregatorInterface;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
class ProductModelProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface, NonBlockingWarningAggregatorInterface
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

    /** @var MediaStorer */
    private $mediaStorer;

    private CleanLineBreaksInTextAttributes $cleanLineBreaksInTextAttributes;

    /** @var Warning[] */
    private array $nonBlockingWarnings = [];

    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ValidatorInterface $validator,
        FilterInterface $productModelFilter,
        ObjectDetacherInterface $objectDetacher,
        AttributeFilterInterface $productModelAttributeFilter,
        MediaStorer $mediaStorer,
        CleanLineBreaksInTextAttributes $cleanLineBreaksInTextAttributes,
        string $importType
    ) {
        parent::__construct($productModelRepository);

        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelRepository = $productModelRepository;
        $this->validator = $validator;
        $this->productModelFilter = $productModelFilter;
        $this->objectDetacher = $objectDetacher;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->importType = $importType;
        $this->mediaStorer = $mediaStorer;
        $this->cleanLineBreaksInTextAttributes = $cleanLineBreaksInTextAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function process($standardProductModel): ?ProductModelInterface
    {
        $baseStandardProductModel = $standardProductModel;
        $parent = $standardProductModel['parent'] ?? '';
        if ($this->importType === self::ROOT_PRODUCT_MODEL && !empty($parent) ||
            $this->importType === self::SUB_PRODUCT_MODEL && empty($parent)
        ) {
            $this->stepExecution->incrementSummaryInfo(sprintf('skipped_in_%s', $this->importType));

            return null;
        }

        if (!isset($standardProductModel['code'])) {
            $this->skipItemWithMessage($standardProductModel, 'The code must be filled');
        }

        $standardProductModel = $this->productModelAttributeFilter->filter($standardProductModel);
        $standardProductModel = $this->filterItemData($standardProductModel);

        $productModel = $this->findOrCreateProductModel($standardProductModel['code']);

        $jobParameters = $this->stepExecution->getJobParameters();
        if ($jobParameters->get('enabledComparison') && null !== $productModel->getId()) {
            // We don't compare immutable fields
            $standardProductModelToCompare = $standardProductModel;
            unset($standardProductModelToCompare['code']);

            $standardProductModel = $this->productModelFilter->filter($productModel, $standardProductModelToCompare);

            if (empty($standardProductModel)) {
                $this->objectDetacher->detach($productModel);
                $this->stepExecution->incrementSummaryInfo('product_model_skipped_no_diff');

                return null;
            }
        }

        if (isset($standardProductModel['values'])) {
            try {
                $standardProductModel['values'] = $this->mediaStorer->store($standardProductModel['values']);
            } catch (InvalidPropertyException $e) {
                $this->objectDetacher->detach($productModel);
                $this->skipItemWithMessage($standardProductModel, $e->getMessage(), $e);
            }

            $cleanedStandardProductModel = $this->cleanLineBreaksInTextAttributes->cleanStandardFormat($standardProductModel);
            foreach ($cleanedStandardProductModel['values'] as $field => $values) {
                if ($values !== $standardProductModel['values'][$field]) {
                    $this->nonBlockingWarnings[] = new Warning(
                        $this->stepExecution,
                        'The value for the "%attribute_code%" attribute contains at least one line break. It or they have been replaced by a space during the import.',
                        ['%attribute_code%' => $field],
                        $baseStandardProductModel
                    );
                }
            }

            $standardProductModel = $cleanedStandardProductModel;
        }

        try {
            $this->productModelUpdater->update($productModel, $standardProductModel);
        } catch (PropertyException $exception) {
            $this->objectDetacher->detach($productModel);
            $message = sprintf('%s: %s', $exception->getPropertyName(), $exception->getMessage());
            $this->skipItemWithMessage($standardProductModel, $message, $exception);
        } catch (AccessDeniedException $exception) {
            $this->objectDetacher->detach($productModel);
            $this->skipItemWithMessage($standardProductModel, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($productModel);

        if ($violations->count() > 0) {
            $this->objectDetacher->detach($productModel);
            $this->skipItemWithConstraintViolations($standardProductModel, $violations);
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

    /**
     * Filters item data to remove associations which are imported through a dedicated processor because we need to
     * create any product models before to associate them
     *
     * @param array $item
     *
     * @return array
     */
    protected function filterItemData(array $item): array
    {
        unset($item['associations']);
        unset($item['quantified_associations']);

        return $item;
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
