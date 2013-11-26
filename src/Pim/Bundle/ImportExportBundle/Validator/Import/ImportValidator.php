<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $identifiers = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface  $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = array())
    {
        $identifier = $this->getIdentifier($columnsInfo, $entity);
        $this->checkIdentifier(get_class($entity), $identifier, $data);
        if (!count($errors)) {
            return $this->getErrors($this->validator->validate($entity));
        } else {
            return $this->validateProperties($entity, $columnsInfo) + $errors;
        }
    }

    /**
     * Checks if the identifier is not already used
     *
     * @param string $class
     * @param mixed  $identifier
     * @param array  $data
     *
     * @throws InvalidItemException
     */
    protected function checkIdentifier($class, $identifier, $data)
    {
        if (!isset($this->identifiers[$class])) {
            $this->identifiers[$class] = array();
        } elseif (in_array($identifier, $this->identifiers[$class])) {
            throw new InvalidItemException(
                $this->translator->trans(
                    'Twin ! the entity "%identifier%" has already been processed',
                    array('%identifier%' => $identifier)
                ),
                $data
            );
        }
        $this->identifiers[$class][] = $identifier;
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
        return $entity->getCode();
    }

    /**
     * Validates the properties of an entity
     *
     * @param type  $entity
     * @param array $columnsInfo
     *
     * @return array
     */
    protected function validateProperties($entity, array $columnsInfo)
    {
        $errors = array();
        foreach ($columnsInfo as $label=>$columnInfo) {
            $violations = $this->validator->validateProperty($entity, $columnInfo['propertyPath']);
            if ($violations->count()) {
                $errors[$label] = $this->getError($violations);
            }
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
    protected function getErrors(ConstraintViolationListInterface $violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $path = $violation->getPath();
            if (!isset($errors[$path])) {
                $errors[$path] = array();
            }
            $errors[$path][] = $violation;
        }

        return array_map($errors, array($this, 'getError'));
    }

    protected function getError($violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $errors[] = $this->getViolationError($violation);
        }

        return explode(', ', $errors);
    }

    protected function getViolationError(ConstraintViolationInterface $violation)
    {
        return $this->translator->trans($violation->getMessageTemplate(), $violation->getMessageParameters());
    }
}
