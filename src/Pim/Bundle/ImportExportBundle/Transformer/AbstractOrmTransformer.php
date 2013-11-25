<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;
use Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Transforms an array in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractOrmTransformer
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var GuesserInterface
     */
    protected $guesser;

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
    protected $transformers = array();

    /**
     * @var array
     */
    protected $columnInfos = array();

    /**
     * Constructor
     *
     * @param RegistryInterface         $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     * @param GuesserInterface          $guesser
     * @param LabelTransformerInterface $labelTransformer
     * @param TranslatorInterface       $translator
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        LabelTransformerInterface $labelTransformer,
        TranslatorInterface $translator
    ) {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
        $this->guesser = $guesser;
        $this->labelTransformer = $labelTransformer;
        $this->translator = $translator;
    }

    /**
     * Transforms an array into an entity
     *
     * @param string $class
     * @param array  $data
     *
     * @throws InvalidItemException
     * @return object
     */
    protected function doTransform($class, array $data, array $mapping = array(), array $defaults = array())
    {
        $this->mapValues($data, $mapping);
        $entity = $this->getEntity($class, $data);
        $this->setDefaultValues($entity, $defaults);
        $errors = $this->setProperties($class, $entity, $data);
        if (count($errors)) {
            throw new InvalidItemException(implode("\n", $errors), $data);
        }

        return $entity;
    }

    protected function setProperties($class, $entity, array $data)
    {
        $errors = array();

        foreach ($data as $label => $value) {
            $columnInfo = $this->getColumnInfo($class, $label);
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            if (!$transformerInfo) {
                throw new UnknownColumnException(array($label));
            }
            $errors = array_merge(
                $errors,
                $this->setProperty($entity, $columnInfo, $transformerInfo, $value)
            );
        }

        return $errors;
    }

    /**
     * Sets a property of the object
     *
     * @param object                       $entity
     * @param array                        $columnInfo
     * @param PropertyTransformerInterface $transformer
     * @param array                        $transformerOptions
     * @param mixed                        $value
     */
    protected function setProperty($entity, array $columnInfo, array $transformerInfo, $value)
    {
        try {
            $value = $transformerInfo[0]->transform($value, $transformerInfo[1]);
        } catch (\Pim\Bundle\ImportExportBundle\Exception\InvalidValueException $ex) {
            return array($this->getTranslatedExceptionMessage($columnInfo['label'], $ex));
        }

        if ($transformerInfo[0] instanceof EntityUpdaterInterface) {
            $transformerInfo[0]->setValue($entity, $columnInfo, $value, $transformerInfo[1]);
        } else {
            $this->propertyAccessor->setValue($entity, $columnInfo['propertyPath'], $value);
        }

        return array();
    }

    /**
     * Finds or creates an entity for given class and data
     *
     * @param string $class
     * @param array  $data
     *
     * @return object
     */
    abstract protected function getEntity($class, array $data);

    /**
     * Returns the transformer info for a column
     *
     * @param string $class
     * @param array  $columnInfo
     *
     * @throws UnknownColumnException
     * @return array
     */
    protected function getTransformerInfo($class, $columnInfo)
    {
        $label = $columnInfo['label'];
        if (!isset($this->transformers[$class][$label])) {
            if (!isset($this->transformers[$class])) {
                $this->transformers[$class] = array();
            }
            $this->transformers[$class][$label] = $this->guesser->getTransformerInfo(
                $columnInfo,
                $this->doctrine->getManager()->getClassMetadata($class)
            );

            if (!$this->transformers[$class][$label]) {
                throw new UnknownColumnException(sprintf('Property path %s not configured for class %s', $label, $class));
            }
        }

        return $this->transformers[$class][$label];
    }

    /**
     * Returns the information for a column label
     *
     * @param string $class
     * @param string $label
     *
     * @return array
     */
    protected function getColumnInfo($class, $label)
    {
        if (!isset($this->columnInfos[$class][$label])) {
            if (!isset($this->columnInfos[$class])) {
                $this->columnInfos[$class] = array();
            }
            $this->columnInfos[$class][$label] = $this->labelTransformer->transform($label);
        }

        return $this->columnInfos[$class][$label];
    }

    /**
     * Remaps values according to $mapping
     *
     * @param array &$values
     * @param array $mapping
     */
    protected function mapValues(array &$values, array $mapping)
    {
        foreach ($mapping as $oldName => $newName) {
            if ($oldName != $newName && isset($values[$oldName])) {
                $values[$newName] = $values[$oldName];
                unset($values[$oldName]);
            }
        }
    }

    /**
     * Sets the default values of the product
     *
     * @param object $product
     * @param array  $defaults
     */
    protected function setDefaultValues($product, array $defaults)
    {
        foreach ($defaults as $propertyPath => $value) {
            $this->propertyAccessor->setValue($product, $propertyPath, $value);
        }
    }

    /**
     * Returns a translated InvalidValueException message
     *
     * @param string                $propertyPath
     * @param InvalidValueException $exception
     *
     * @return string
     */
    public function getTranslatedExceptionMessage($propertyPath, InvalidValueException $exception)
    {
        return $this->getTranslatedErrorMessage(
            $propertyPath,
            $exception->getRawMessage(),
            $exception->getMessageParameters()
        );
    }

    /**
     * Returns a translated error message
     *
     * @param string $propertyPath
     * @param string $message
     * @param array  $parameters
     *
     * @return string
     */
    public function getTranslatedErrorMessage($propertyPath, $message, array $parameters = array())
    {
        return sprintf(
            '%s: %s',
            $propertyPath,
            $this->translator->trans($message, $parameters)
        );
    }
}
