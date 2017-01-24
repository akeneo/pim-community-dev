<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\FileInvalidItem;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;
use Pim\Component\Connector\Exception\MissingIdentifierException;
use Pim\Component\Connector\Item\BulkCompositeIdentifierBag;
use Pim\Component\Connector\Item\BulkSimpleIdentifierBag;
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
        $properties = $repository->getIdentifierProperties();
        $references = [];
        foreach ($properties as $property) {
            if (!isset($data[$property])) {
                throw new MissingIdentifierException(sprintf(
                    'Missing identifier column "%s". Columns found: %s.',
                    $property,
                    implode(', ', array_keys($data))
                ));
            }
            $references[] = $data[$property];
        }

        return $repository->findOneByIdentifier(implode('.', $references));
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

        $invalidItem = new FileInvalidItem(
            $item,
            ($this->stepExecution->getSummaryInfo('item_position'))
        );

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

        throw new InvalidItemFromViolationsException(
            $violations,
            new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
            [],
            0,
            $previousException
        );
    }

    /**
     * Stores an identifier in the bag "bulk_identifier_bag",to be able to check duplications.
     * The bag should be reset after each bulk is processed. Typically, in the Writer.
     *
     * @param array $item
     * @param mixed $identifier
     */
    protected function checkIdentifierDuplication(array $item, $identifier)
    {
        if (is_array($identifier) && 1 === count($identifier)) {
            $identifier = current($identifier);
        }

        if (null === $bag = $this->stepExecution->getExecutionContext()->get('bulk_identifier_bag')) {
            $bag = new BulkSimpleIdentifierBag();

            if (is_array($identifier)) {
                $bag = new BulkCompositeIdentifierBag();
            }

            $this->stepExecution->getExecutionContext()->put('bulk_identifier_bag', $bag);
        }

        if ($bag->has($identifier)) {
            $this->skipItemWithMessage(
                $item,
                sprintf('An item with the identifier "%s" has already been processed.', $identifier)
            );
        }

        $bag->add($identifier);
    }
}
