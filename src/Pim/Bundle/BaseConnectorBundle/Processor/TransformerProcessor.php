<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\ImportExportBundle\Transformer\EntityTransformerInterface;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Abstract processor for transformer based imports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformerProcessor extends AbstractConfigurableStepElement implements
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
     * @var EntityTransformerInterface
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    /**
    * @var boolean
    */
    protected $skipEmpty;

    /**
     * @var array
     */
    protected $mapping = array();

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface   $validator
     * @param TranslatorInterface        $translator
     * @param EntityTransformerInterface $transformer
     * @param string                     $class
     * @param boolean                    $skipEmpty
     */
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        $class,
        $skipEmpty = false
    ) {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->transformer = $transformer;
        $this->class = $class;
        $this->skipEmpty = $skipEmpty;
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
            $this->setItemErrors($item, $errors);
        } else {
            return $entity;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Adds a field mapping
     *
     * @param string $original The name of the field as supplied by the reader
     * @param string $target   The name of the field which will be sent to the transformer
     */
    public function addMapping($original, $target)
    {
        $this->mapping[$original] = $target;
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
            foreach (array_keys($values) as $key) {
                if (!is_array($values[$key]) && (null === $values[$key] || '' === trim($values[$key]))) {
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
     *
     * @return array
     */
    protected function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets errors on items
     *
     * @param array $item
     * @param array $errors
     *
     * @throws InvalidItemException
     */
    protected function setItemErrors(array $item, array $errors)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }
        throw new InvalidItemException(implode("\n", $this->getErrorMessages($errors)), $item);
    }

    /**
     * Transforms the array in an object
     *
     * @param array $item
     *
     * @return object
     */
    protected function transform($item)
    {
        return $this->transformer->transform($this->class, $item);
    }

    /**
     * Returns the column info of the transformed fields
     *
     * @return array
     */
    protected function getTransformedColumnsInfo()
    {
        return $this->transformer->getTransformedColumnsInfo($this->class);
    }

    /**
     * Returns an array of errors for each columns
     *
     * @return array
     */
    protected function getTransformerErrors()
    {
        return $this->transformer->getErrors($this->class);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
