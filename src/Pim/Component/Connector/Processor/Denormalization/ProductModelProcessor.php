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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
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

    /**
     * @param SimpleFactoryInterface                $productModelFactory
     * @param ObjectUpdaterInterface                $productModelUpdater
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param ValidatorInterface                    $validator
     * @param FilterInterface                       $productModelFilter
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ValidatorInterface $validator,
        FilterInterface $productModelFilter,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelRepository = $productModelRepository;
        $this->validator = $validator;
        $this->productModelFilter = $productModelFilter;
        $this->objectDetacher = $objectDetacher;
    }


    /**
     * {@inheritdoc}
     */
    public function process($flatProductModel): ?ProductModelInterface
    {
        if (!isset($flatProductModel['identifier'])) {
            $this->skipItemWithMessage($flatProductModel, 'The identifier must be filled');
        }

        $productModel = $this->findOrCreateProductModel($flatProductModel['identifier']);

        $jobParameters = $this->stepExecution->getJobParameters();
        if ($jobParameters->get('enabledComparison')) {
            $flatProductModel = $this->productModelFilter->filter($productModel, $flatProductModel);

            if (empty($flatProductModel) && null !== $productModel->getId()) {
                $this->objectDetacher->detach($productModel);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

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
     * @param string $identifier
     *
     * @return ProductModelInterface
     */
    private function findOrCreateProductModel(string $identifier): ProductModelInterface
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($identifier);
        if (!$productModel) {
            $productModel = $this->productModelFactory->create();
        }

        return $productModel;
    }
}
