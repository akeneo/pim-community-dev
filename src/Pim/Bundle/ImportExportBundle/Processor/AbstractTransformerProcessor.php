<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;

/**
 * Abstract processor for transformer based imports
 *
 * @abstract
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractTransformerProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
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
     * @var boolean
     */
    protected $skipEmpty = false;

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
        $entity = $this->transform($item);
        $errors = $this->getTransformerErrors();

        $errors = $this->validator->validate($entity, $this->getTransformedColumnsInfo(), $item, $errors);

        if (count($errors)) {
            if ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('skip');
            }
            throw new InvalidItemException(implode("\n", $this->getErrorMessages($errors)), $item);
        }

        return $entity;
    }

    /**
     * Returns an array of translated field errors
     *
     * @param array $errors
     *
     * @return array
     */
    protected function getErrorMessages(array $errors)
    {
        return array_map(
            function ($fieldErrors, $label) {
                return sprintf(
                    '%s: %s',
                    $label,
                    implode(
                        ',',
                        array_map(
                            function ($args) {
                                return call_user_func_array(array($this->translator, 'trans'), $args);
                            },
                            $fieldErrors
                        )
                    )
                );
            },
            array_values($errors),
            array_keys($errors)
        );
    }

    /**
     * Remaps values according to $mapping
     *
     * @param array &$values
     */
    protected function mapValues(array &$values)
    {
        foreach ($this->getMapping() as $oldName => $newName) {
            if ($oldName != $newName && isset($values[$oldName])) {
                $values[$newName] = $values[$oldName];
                unset($values[$oldName]);
            }
        }
        if ($this->skipEmpty) {
            foreach(array_keys($values) as $key) {
                if (null === $values[$key] || '' === trim($values[$key])) {
                    unset($values[$key]);
                }
            }
        }
    }

    /**
     * Returns an array of mapped fields
     *
     * The keys correspond to the originally read columns labels.
     * The values correspond to the column labels needed by the transformer
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

    /**
     * Transforms the array in an object
     *
     * @param array $item
     *
     * @abstract
     * @return object
     */
    abstract protected function transform($item);

    /**
     * Returns the column info of the transformed fields
     *
     * @abstract
     * @return array
     */
    abstract protected function getTransformedColumnsInfo();

    /**
     * Returns an array of errors for each columns
     *
     * @abstract
     * @return array
     */
    abstract protected function getTransformerErrors();

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
