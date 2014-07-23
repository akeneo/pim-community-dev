<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Import;

use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\BaseConnectorBundle\Exception\DuplicateIdentifierException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validates an imported entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportValidator implements ImportValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $identifiers = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = array())
    {
        $this->checkIdentifier($entity, $columnsInfo, $data);
        if (!count($errors)) {
            return $this->getErrorMap($this->validator->validate($entity));
        } else {
            return $this->validateProperties($entity, $columnsInfo) + $errors;
        }
    }

    /**
     * Checks if the identifier is not already used
     *
     * @param object $entity
     * @param array  $columnsInfo
     * @param array  $data
     *
     * @throws DuplicateIdentifierException
     */
    protected function checkIdentifier($entity, array $columnsInfo, $data)
    {
        $identifier = $this->getIdentifier($columnsInfo, $entity);
        if (!$identifier) {
            return;
        }

        $class = get_class($entity);
        if (!isset($this->identifiers[$class])) {
            $this->identifiers[$class] = array();
        } elseif (isset($this->identifiers[$class][$identifier])) {
            throw new DuplicateIdentifierException($identifier, $data);
        }
        $this->identifiers[$class][$identifier] = "";
    }

    /**
     * Returns the identifier of the entity
     *
     * @param array  $columnsInfo
     * @param object $entity
     *
     * @return mixed
     */
    protected function getIdentifier(array $columnsInfo, $entity)
    {
        return ($entity instanceof ReferableInterface)
                ? $entity->getReference()
                : null;
    }

    /**
     * Validates the properties of an entity
     *
     * @param object $entity
     * @param array  $columnsInfo
     *
     * @return array
     */
    protected function validateProperties($entity, array $columnsInfo)
    {
        $errors = array();
        foreach ($columnsInfo as $columnInfo) {
            $violations = $this->validator->validateProperty($entity, $columnInfo->getPropertyPath());
            if ($violations->count()) {
                $errors[$columnInfo->getLabel()] = $this->getErrorArray($violations);
            }
        }

        return $errors;
    }

    /**
     * Returns an array of field error arrays for a list of violations
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    protected function getErrorMap(ConstraintViolationListInterface $violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if (!isset($errors[$path])) {
                $errors[$path] = array();
            }
            $errors[$path][] = $this->getViolationError($violation);
        }

        return $errors;
    }

    /**
     * Returns an array of errors for a list of violations
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    protected function getErrorArray(ConstraintViolationListInterface $violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $errors[] = $this->getViolationError($violation);
        }

        return $errors;
    }

    /**
     * Returns an error array for a constraint violation
     *
     * @param ConstraintViolationInterface $violation
     *
     * @return array
     */
    protected function getViolationError(ConstraintViolationInterface $violation)
    {
        return array($violation->getMessageTemplate(), $violation->getMessageParameters());
    }
}
