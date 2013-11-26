<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Description of OrmImportValidator
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
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

    public function validate($entity, array $columnsInfo, array $data, array $errors = array())
    {
        $this->checkIdentifier($this->getIdentifier($columnsInfo, $entity), $data);
        if (!count($errors)) {
            return $this->getErrors($this->validator->validate($entity));
        } else {
            return $this->validateProperties($entity, $columnsInfo) + $errors;
        }
    }

    protected function checkIdentifier($identifier, $data)
    {
        $class = get_class($data);
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
    }

    protected function getIdentifier(array $columnsInfo, $entity)
    {
        return $entity->getCode();
    }

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
