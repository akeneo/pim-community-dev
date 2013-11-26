<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Exception\TransformerException;
use Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\SkipTransformer;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * @var array
     */
    protected $transformers = array();

    /**
     * @var array
     */
    protected $transformedColumnsInfo;

    /**
     * Constructor
     *
     * @param RegistryInterface         $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     * @param GuesserInterface          $guesser
     * @param LabelTransformerInterface $labelTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        LabelTransformerInterface $labelTransformer
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
        $this->transformedColumnsInfo = array();
        $this->mapValues($data, $mapping);
        $entity = $this->getEntity($class, $data);
        $this->setDefaultValues($entity, $defaults);
        $errors = $this->setProperties($class, $entity, $data);
        if (count($errors)) {
            throw new TransformerException($errors);
        }

        return $entity;
    }

    protected function setProperties($class, $entity, array $data)
    {
        $errors = array();

        foreach ($data as $label => $value) {
            $columnInfo = $this->labelTransformer->transform($class, $label);
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
            $this->propertyAccessor->setValue($entity, $columnInfo['propertyPath'], $value);
        }

        $this->transformedColumnsInfo[] = $columnInfo;

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
