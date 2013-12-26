<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\SkipTransformer;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\ImportExportBundle\Exception\MissingIdentifierException;

/**
 * Transforms an array in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractORMTransformer
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
    protected $columnInfoTransformer;

    /**
     * @var array
     */
    protected $transformers = array();

    /**
     * @var array
     */
    protected $transformedColumnsInfo = array();

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer
    ) {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
        $this->guesser = $guesser;
        $this->columnInfoTransformer = $columnInfoTransformer;
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
     *
     * @return array
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
     * @param array  $defaults
     *
     * @throws InvalidItemException
     *
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

    /**
     * Sets the properties of the entity
     *
     * @param string $class
     * @param object $entity
     * @param array  $data
     */
    protected function setProperties($class, $entity, array $data)
    {
        foreach ($data as $label => $value) {
            $columnInfo = $this->columnInfoTransformer->transform($class, $label);
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
     * Returns an array with the error and its parameters, or null if no error encountered
     *
     * @param object              $entity
     * @param ColumnInfoInterface $columnInfo
     * @param array               $transformerInfo
     * @param mixed               $value
     *
     * @return array|null
     */
    protected function setProperty($entity, ColumnInfoInterface $columnInfo, array $transformerInfo, $value)
    {
        if ($transformerInfo[0] instanceof SkipTransformer) {
            return;
        }

        try {
            $value = $transformerInfo[0]->transform($value, $transformerInfo[1]);
        } catch (PropertyTransformerException $ex) {
            return array($ex->getMessageTemplate(), $ex->getMessageParameters());
        }

        if ($transformerInfo[0] instanceof EntityUpdaterInterface) {
            $transformerInfo[0]->setValue($entity, $columnInfo, $value, $transformerInfo[1]);
        } else {
            $this->propertyAccessor->setValue($entity, $columnInfo->getPropertyPath(), $value);
        }

        $this->transformedColumnsInfo[] = $columnInfo;
    }

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
     * @param object $object
     * @param array  $defaults
     */
    protected function setDefaultValues($object, array $defaults)
    {
        foreach ($defaults as $propertyPath => $value) {
            $this->propertyAccessor->setValue($object, $propertyPath, $value);
        }
    }

    /**
     * Finds or creates an entity for given class and data
     *
     * @param string $class
     * @param array  $data
     *
     * @abstract
     * @return object
     */
    protected function getEntity($class, array $data)
    {
        $object = $this->findEntity($class, $data);
        if (!$object) {
            $object = $this->createEntity($class, $data);
        }

        return $object;
    }

    /**
     * Finds an entity
     *
     * @param string $class
     * @param array  $data
     *
     * @return object|null
     */
    protected function findEntity($class, array $data)
    {
        $repository = $this->doctrine->getRepository($class);

        if ($repository instanceof ReferableEntityRepositoryInterface) {
            $reference = implode(
                '.',
                array_map(
                    function ($property) use ($class, $data) {
                        if (!isset($data[$property])) {
                            throw new MissingIdentifierException();
                        }

                        return $data[$property];
                    },
                    $repository->getReferenceProperties()
                )
            );

            return $this->doctrine->getRepository($class)->findByReference($reference);
        } else {
            return null;
        }
    }

    /**
     * Creates an entity of the given class
     *
     * @param string $class
     * @param array  $data
     *
     * @return object
     */
    protected function createEntity($class, array $data)
    {
        return new $class;
    }
}
