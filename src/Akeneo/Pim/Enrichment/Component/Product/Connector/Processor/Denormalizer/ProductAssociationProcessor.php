<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Product association import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var bool */
    protected $enabledComparison = true;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected FilterInterface $productAssocFilter,
        protected ObjectDetacherInterface $detacher,
    ) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!$this->hasAssociationToImport($item)) {
            $this->stepExecution->incrementSummaryInfo('product_skipped_no_associations');

            return null;
        }

        $item = array_merge(
            [
                'associations' => [],
                'quantified_associations' => [],
            ],
            $item
        );

        if (!isset($item['identifier']) && !isset($item['uuid'])) {
            $this->skipItemWithMessage($item, 'Either the identifier or the uuid must be filled');
        }

        if (isset($item['uuid'])) {
            $product = $this->findProductByUuid($item['uuid'], $item);
            if (null === $product) {
                $this->skipItemWithMessage($item, sprintf('No product with uuid "%s" has been found', $item['uuid']));
            }
        } else {
            $product = $this->findProduct($item['identifier'], $item);
            if (null === $product) {
                $this->skipItemWithMessage($item, sprintf('No product with identifier "%s" has been found', $item['identifier']));
            }
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
        }

        try {
            $this->updateProduct($product, $item);
        } catch (PropertyException
        | InvalidArgumentException
        | AccessDeniedException
        | TwoWayAssociationWithTheSameProductException $exception) {
            $this->detachProduct($product);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateProductAssociations($product);
        $violations->addAll($this->validateProductQuantifiedAssociations($product));
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
     * @param array  $item
     *
     * @return null|ProductInterface
     */
    public function findProduct(string $identifier, array $item): ?ProductInterface
    {
        try {
            return $this->repository->findOneByIdentifier($identifier);
        } catch (AccessDeniedException $e) {
            $this->skipItemWithMessage($item, $e->getMessage(), $e);
        }

        return null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateProductAssociations(ProductInterface $product): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();
        $associations = $product->getAssociations();
        foreach ($associations as $association) {
            $violations->addAll($this->validator->validate($association));
        }

        return $violations;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateProductQuantifiedAssociations(ProductInterface $product): ConstraintViolationListInterface
    {
        return $this->validator->validate($product->getQuantifiedAssociations());
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
     * Is there association(s) in new values?
     */
    protected function hasAssociationToImport(array $item): bool
    {
        if (isset($item['associations'])) {
            foreach ($item['associations'] as $association) {
                $hasProductAssoc = isset($association['products']);
                $hasGroupAssoc = isset($association['groups']);
                $hasProductModelAssoc = isset($association['product_models']);

                if ($hasProductAssoc || $hasGroupAssoc || $hasProductModelAssoc) {
                    return true;
                }
            }
        }

        if (isset($item['quantified_associations'])) {
            foreach ($item['quantified_associations'] as $quantifiedAssociation) {
                $hasProductAssoc = isset($quantifiedAssociation['products']);
                $hasProductModelAssoc = isset($quantifiedAssociation['product_models']);

                if ($hasProductAssoc || $hasProductModelAssoc) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findProductByUuid(string $uuid, array $item): ?ProductInterface
    {
        try {
            Assert::methodExists($this->repository, 'findOneByUuid');
            return $this->repository->findOneByUuid(Uuid::fromString($uuid));
        } catch (AccessDeniedException $e) {
            $this->skipItemWithMessage($item, $e->getMessage(), $e);
        }

        return null;
    }
}
