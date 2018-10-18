<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Basic implementation of a product publisher/unpublisher tasklet
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
abstract class AbstractProductPublisherTasklet implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param PublishedProductManager   $manager
     * @param PaginatorFactoryInterface $paginatorFactory
     * @param ValidatorInterface        $validator
     */
    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator
    ) {
        $this->manager = $manager;
        $this->paginatorFactory = $paginatorFactory;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @param array $filters
     *
     * @return ProductQueryBuilderInterface
     */
    abstract protected function getProductQueryBuilder(array $filters = []);

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder($filters);

        return $productQueryBuilder->execute();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage($violations, $product)
    {
        foreach ($violations as $violation) {
            // TODO re-format the message, property path doesn't exist for class constraint
            // for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string) $invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            }
            $errors = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $invalidValue
            );
            $this->stepExecution->addWarning($errors, [], new DataInvalidItem($product));
        }
    }
}
