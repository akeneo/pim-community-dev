<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\SkipTransformer;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;

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
     * @var ColumnInfoTransformerInterface
     */
    protected $labelTransformer;

    /**
     * @var array
     */
    protected $transformers = array();

    /**
     * @var array
     */
    protected $transformedColumnsInfo;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $labelTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $labelTransformer
    ) {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
        $this->guesser = $guesser;
        $this->labelTransformer = $labelTransformer;
    }

    /**
     * Return infos about the last imported columns
     *
     * @return array
     */
    public function getTransformedColumnsInfo()
    {
        return $this->transformedColumnsInfo;
    }

    /**
     * Returns the errors for the last imported entity
     */
    public function getErrors()
    {
        return $this->errors;
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
    protected function doTransform($class, array $data, array $defaults = array())
    {
        $this->transformedColumnsInfo = array();
        $this->errors = array();
        $entity = $this->getEntity($class, $data);
        $this->setDefaultValues($entity, $defaults);
        $this->setProperties($class, $entity, $data);

        return $entity;
    }

    protected function setProperties($class, $entity, array $data)
    {
        foreach ($data as $label => $value) {
            $columnInfo = $this->labelTransformer->transform($class, $label);
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            $error = $this->setProperty($entity, $columnInfo, $transformerInfo, $value);
            if ($error) {
                $this->errors[$label] = array($error);
            }
        }
    }

    /**
     * Sets a property of the object
     *
     * @param object                       $entity
     * @param ColumnInfoInterface          $columnInfo
     * @param PropertyTransformerInterface $transformer
     * @param array                        $transformerOptions
     * @param mixed                        $value
     */
    protected function setProperty($entity, ColumnInfoInterface $columnInfo, array $transformerInfo, $value)
    {
        if ($transformerInfo[0] instanceof SkipTransformer) {
            return array();
        }

        try {
            $value = $transformerInfo[0]->transform($value, $transformerInfo[1]);
        } catch (PropertyTransformerException $ex) {
            return array($ex->getRawMessage(), $ex->getMessageParameters());
        }

        if ($transformerInfo[0] instanceof EntityUpdaterInterface) {
            $transformerInfo[0]->setValue($entity, $columnInfo, $value, $transformerInfo[1]);
        } else {
            $this->propertyAccessor->setValue($entity, $columnInfo->getPropertyPath(), $value);
        }

        $this->transformedColumnsInfo[] = $columnInfo;
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
     * @param string              $class
     * @param ColumnInfoInterface $columnInfo
     *
     * @return array
     */
    protected function getTransformerInfo($class, ColumnInfoInterface $columnInfo)
    {
        $label = $columnInfo->getLabel();
        if (!isset($this->transformers[$class][$label])) {
            if (!isset($this->transformers[$class])) {
                $this->transformers[$class] = array();
            }
            $this->transformers[$class][$label] = $this->guesser->getTransformerInfo(
                $columnInfo,
                $this->doctrine->getManager()->getClassMetadata($class)
            );
            if (!$this->transformers[$class][$label]) {
                throw new UnknownColumnException(array($label));
            }
        }

        return $this->transformers[$class][$label];
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
}
