<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Transformer\LabelTransformerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Zend\Validator\ValidatorInterface;

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
     * @var LabelTransformerInterface
     */
    protected $labelTransformer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $identifiers = array();

    public function validate($entity, array $data, array $errors = array())
    {
        $class = get_class($entity);
        $this->checkIdentifier($class, $this->getIdentifier($entity), $data);
        if (!count($errors)) {
            return $this->getErrors($this->validator->validate($entity));
        } else {
            return $this->validateProperties($class, $entity, $data, $errors);
        }
    }

    protected function checkIdentifier($class, $identifier, $data)
    {
        if (!isset($this->identifiers[$class])) {
            $this->identifiers[$class] = array();
        } elseif (in_array($identifier, $this->identifiers[$class])) {
            throw new InvalidItemException(
                $this->translator->trans(
                    'Twin ! the entity "%identifier%" has already been processed',
                    array('%identifier%' => $identifier)
                )
            );
        }
    }

    protected function getIdentifier($entity)
    {
        return $entity->getCode();
    }

    protected function validateProperties($class, $entity, $data, $errors) {
        $columnInfos = $this->labelTransformer->transform($class, array_keys($data));
        foreach ($columnInfos as $label=>$columnInfo) {
            if (!isset($errors[$label])) {
                $errors[$label] = $this->getError(
                    $this->validator->validateProperty($entity, $columnInfo['propertyPath'])
                );
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
        foreach($violations as $violation) {
            $errors[] = $this->getViolationError($violation);
        }

        return explode(', ', $errors);
    }

    protected function getViolationError(ConstraintViolationInterface $violation)
    {
        return $this->translator->trans($violation->getMessageTemplate(), $violation->getMessageParameters());
    }
}
