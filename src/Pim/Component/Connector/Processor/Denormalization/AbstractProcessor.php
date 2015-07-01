<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\Exception\MissingIdentifierException;
use Symfony\Component\Validator\ConstraintViolationInterface;
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
abstract class AbstractProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
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
    public function getConfigurationFields()
    {
        return [];
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
                throw new MissingIdentifierException();
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

        throw new InvalidItemException($message, $item, [], 0, $previousException);
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

        $errors = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            // TODO re-format the message, property path doesn't exist for class constraint
            // for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string) $invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            }
            $errors[] = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $invalidValue
            );
        }

        throw new InvalidItemException(implode("\n", $errors), $item, [], 0, $previousException);
    }
}
