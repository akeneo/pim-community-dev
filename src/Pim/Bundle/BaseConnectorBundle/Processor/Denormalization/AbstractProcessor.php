<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Abstract processor to provide a way to denormalize array data to object by,
 * - fetch an existing object or create it
 * - denormalize item to update the object
 * - validate the object
 * - skip the object if it contains invalid data.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5, please use to \Pim\Component\Connector\Processor\Denormalization\
 */
abstract class AbstractProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var string */
    protected $class;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository   repository to search the object in
     * @param DenormalizerInterface                 $denormalizer denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator    validator of the object
     * @param ObjectDetacherInterface               $detacher     object detacher
     * @param string                                $class        class of the object to instanciate in case if need
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        $class
    ) {
        $this->repository = $repository;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->detacher = $detacher;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Try to find an object according to its identifiers from a repository or create an empty object
     * if it does not exist.
     *
     * @param IdentifiableObjectRepositoryInterface $repository the repository to search inside
     * @param array                                 $data       the data that is currently processed
     * @param string                                $class      the class to instanciate in case the
     *                                                          object has not been found
     *
     * @return object
     */
    protected function findOrCreateObject(IdentifiableObjectRepositoryInterface $repository, array $data, $class)
    {
        if (null !== $object = $this->findObject($repository, $data)) {
            return $object;
        }

        return new $class();
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
     * Detaches the object from the unit of work.
     *
     * Detach an object from the UOW is the responsibility of the writer, but to do so, it should know the
     * skipped items or we should use an explicit persist strategy
     *
     * @param mixed $object
     */
    protected function detachObject($object)
    {
        $this->detacher->detach($object);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception.
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
