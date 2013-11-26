<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Exception;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\Exception\TransformerException;
use Pim\Bundle\ImportExportBundle\Exception\TranslatableExceptionInterface;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Description of AbstractProcessor
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
abstract class AbstractTransformerProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @var ImportValidatorInterface
     */
    protected $validator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface $validator
     * @param TranslatorInterface      $translator
     */
    public function __construct(ImportValidatorInterface $validator, TranslatorInterface $translator)
    {
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $this->mapValues($item);
        try {
            $entity = $this->transform($item);
        } catch (Exception $ex) {
            if ($ex instanceof TranslatableExceptionInterface) {
                $ex->translateMessage($this->translator);
            }
            throw $ex;
        }
        $errors = array_map(
            function ($args) {
                return call_user_func_array(array($this->translator, 'trans'), $args);
            },
            $this->getTransformerErrors()
        );
        $this->validator->validate($entity, $this->getTransformedColumnsInfo(), $item, $errors);

        if (count($errors)) {
            throw new InvalidItemException(implode("\n", $errors), $item);
        }

        return $entity;
    }

    /**
     * Remaps values according to $mapping
     *
     * @param array &$values
     * @param array $mapping
     */
    protected function mapValues(array &$values)
    {
        foreach ($this->getMapping() as $oldName => $newName) {
            if ($oldName != $newName && isset($values[$oldName])) {
                $values[$newName] = $values[$oldName];
                unset($values[$oldName]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    abstract protected function transform($item);

    abstract protected function getTransformedColumnsInfo();
    
    abstract protected function getTransformerErrors();
}
