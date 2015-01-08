<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Abstract processor to transform array data to object
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

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ReferableEntityRepositoryInterface */
    protected $repository;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var string */
    protected $class;

    /**
     * @param ReferableEntityRepositoryInterface $repository   repository to search the object in
     * @param ValidatorInterface                 $validator    validator of the object
     * @param DenormalizerInterface              $denormalizer denormalizer used to transform array to object
     * @param string                             $class        class of the object to instanciate in case if need
     */
    public function __construct(
        ReferableEntityRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        $class
    ) {
        $this->repository = $repository;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
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
     * @param ReferableEntityRepositoryInterface $repository the repository to search inside
     * @param array                              $data       the data that is currently processed
     * @param string                             $class      the class to instanciate in case the
     *                                                       object has not been found
     *
     * @return object
     */
    protected function findOrCreateObject(ReferableEntityRepositoryInterface $repository, array $data, $class)
    {
        if (null !== $object = $this->findObject($repository, $data)) {
            return $object;
        }

        return new $class();
    }

    /**
     * Find an object according to its identifiers from a repository.
     *
     * @param ReferableEntityRepositoryInterface $repository the repository to search inside
     * @param array                              $data       the data that is currently processed
     *
     * @return object|null
     *
     * @throws MissingIdentifierException in case the processed data do not allow to retrieve an object
     *                                    by its identifiers properly
     */
    protected function findObject(ReferableEntityRepositoryInterface $repository, array $data)
    {
        $properties = $repository->getReferenceProperties();
        $references = [];
        foreach ($properties as $property) {
            if (!isset($data[$property])) {
                throw new MissingIdentifierException();
            }
            $references[] = $data[$property];
        }

        return $repository->findByReference(implode('.', $references));
    }

    /**
     * Sets an item as skipped and throws an invalid item exception with the message.
     *
     * @param array  $item
     * @param string $message
     *
     * @throws InvalidItemException
     */
    protected function skipItemWithMessage(array $item, $message)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        throw new InvalidItemException($message, $item);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception.
     *
     * @param array      $item
     * @param \Exception $e
     *
     * TODO : replace handleExceptionOnItem by this one
     *
     * @throws InvalidItemException
     */
    protected function skipItemWithPreviousException(array $item, \Exception $e)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        throw new InvalidItemException($e->getMessage(), $item, [], 0, $e);
    }

    /**
     * Sets an item as skipped and throws an invalid item exception.
     *
     * @param array                            $item
     * @param ConstraintViolationListInterface $violations
     *
     * TODO : replace handleConstraintViolationsOnItem by this one
     *
     * @throws InvalidItemException
     */
    protected function skipItemWithConstraintViolations(array $item, ConstraintViolationListInterface $violations)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        $errors = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errors[] = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $violation->getInvalidValue()
            );
        }

        throw new InvalidItemException(implode("\n", $errors), $item);
    }
}
