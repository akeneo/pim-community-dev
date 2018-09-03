<?php

namespace Akeneo\Tool\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Exception\MissingIdentifierException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Abstract processor to provide a way to denormalize array data to object by,
 * - fetch an existing object or create it
 * - update the object
 * - skip the object if it contains invalid data
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProcessor implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository repository to search the object in
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Find an object according to its identifiers from a repository.
     *
     * @param IdentifiableObjectRepositoryInterface $repository the repository to search inside
     * @param array                                 $data       the data that is currently processed
     *
     * @throws MissingIdentifierException in case the processed data do not allow to retrieve an object
     *                                    by its identifiers properly
     *
     * @return object|null
     */
    protected function findObject(IdentifiableObjectRepositoryInterface $repository, array $data)
    {
        $identifier = $this->getItemIdentifier($repository, $data);

        return $repository->findOneByIdentifier($identifier);
    }

    /**
     * Get the identifier of a processed item
     *
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $item
     *
     * @throws MissingIdentifierException if the processed item doesn't contain the identifier properties
     *
     * @return string
     */
    protected function getItemIdentifier(IdentifiableObjectRepositoryInterface $repository, array $item)
    {
        $properties = $repository->getIdentifierProperties();
        $references = [];
        foreach ($properties as $property) {
            if (!isset($item[$property])) {
                throw new MissingIdentifierException(sprintf(
                    'Missing identifier column "%s". Columns found: %s.',
                    $property,
                    implode(', ', array_keys($item))
                ));
            }
            $references[] = $item[$property];
        }

        return implode('.', $references);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception
     *
     * @param array      $item
     * @param \Exception $previousException
     * @param string     $message
     *
     * @throws InvalidItemException
     */
    protected function skipItemWithMessage(array $item, $message, \Exception $previousException = null)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;

        $invalidItem = new FileInvalidItem($item, $itemPosition);

        throw new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception.
     *
     * @param array                            $item
     * @param ConstraintViolationListInterface $violations
     * @param \Exception                       $previousException
     *
     * @throws InvalidItemException
     */
    protected function skipItemWithConstraintViolations(
        array $item,
        ConstraintViolationListInterface $violations,
        \Exception $previousException = null
    ) {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;

        throw new InvalidItemFromViolationsException(
            $violations,
            new FileInvalidItem($item, $itemPosition),
            [],
            0,
            $previousException
        );
    }
}
