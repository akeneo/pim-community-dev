<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelAssociationProcessor extends AbstractProcessor implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
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
    protected $associationsFilter;

    /** @var bool */
    protected $enabledComparison = true;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param FilterInterface                       $productAssocFilter
     * @param ObjectDetacherInterface               $detacher
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
        $this->associationsFilter = $productAssocFilter;
        $this->detacher = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!$this->hasAssociationToImport($item)) {
            $this->stepExecution->incrementSummaryInfo('product_model_skipped_no_associations');

            return null;
        }

        $item = array_merge(
            ['associations' => []],
            $item
        );

        if (!isset($item['code'])) {
            $this->skipItemWithMessage($item, 'The code must be filled');
        }

        $entity = $this->findEntity($item['code'], $item);
        if (null === $entity) {
            $this->skipItemWithMessage($item, sprintf('No product model with code "%s" has been found', $item['code']));
        }

        $parameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $parameters->get('enabledComparison');
        if ($enabledComparison) {
            $item = $this->filterIdenticalData($entity, $item);

            if (empty($item)) {
                $this->detach($entity);
                $this->stepExecution->incrementSummaryInfo('product_model_skipped_no_diff');

                return null;
            }
        }

        try {
            $this->update($entity, $item);
        } catch (PropertyException | InvalidArgumentException | AccessDeniedException $exception) {
            $this->detach($entity);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateAssociations($entity);
        if ($violations && $violations->count() > 0) {
            $this->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $entity;
    }

    /**
     * @param ProductModelInterface $product
     * @param array                 $item
     *
     * @return array
     */
    protected function filterIdenticalData(ProductModelInterface $product, array $item): array
    {
        return $this->associationsFilter->filter($product, $item);
    }

    /**
     * @param ProductModelInterface $productModel
     * @param array                 $item
     *
     * @throws PropertyException
     */
    protected function update(ProductModelInterface $productModel, array $item): void
    {
        $this->updater->update($productModel, $item);
    }

    /**
     * @param string $identifier
     * @param array  $item
     *
     * @return null|ProductModelInterface
     *
     * @throws \Akeneo\Tool\Component\Batch\Item\InvalidItemException
     */
    public function findEntity(string $identifier, array $item): ?ProductModelInterface
    {
        try {
            return $this->repository->findOneByIdentifier($identifier);
        } catch (AccessDeniedException $e) {
            $this->skipItemWithMessage($item, $e->getMessage(), $e);
        }

        return null;
    }

    /**
     * @param EntityWithAssociationsInterface $product
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface|null
     */
    protected function validateAssociations(EntityWithAssociationsInterface $product): ?ConstraintViolationListInterface
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
     * Detaches the product model from the unit of work is the responsibility of the writer but in this case we
     * want ensure that an updated and invalid product will not be used in the association processor.
     * Also we don't want to keep skipped product models in memory
     *
     * @param EntityWithAssociationsInterface $productModel
     */
    protected function detach(EntityWithAssociationsInterface $productModel): void
    {
        $this->detacher->detach($productModel);
    }

    /**
     * It there association(s) in new values ?
     *
     * @param array $item
     *
     * @return bool
     */
    protected function hasAssociationToImport(array $item): bool
    {
        if (!isset($item['associations'])) {
            return false;
        }

        foreach ($item['associations'] as $association) {
            $hasProductAssoc = isset($association['products']);
            $hasGroupAssoc = isset($association['groups']);
            $hasProductModelAssoc = isset($association['product_models']);

            if ($hasProductAssoc || $hasGroupAssoc || $hasProductModelAssoc) {
                return true;
            }
        }

        return false;
    }
}
