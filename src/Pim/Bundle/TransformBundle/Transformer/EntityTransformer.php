<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;
use Pim\Bundle\TransformBundle\Exception\UnknownColumnException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\SkipTransformer;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Transforms an array in an entity
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformer implements EntityTransformerInterface
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
    protected $colInfoTransformer;

    /**
     * @var array
     */
    protected $transformers = array();

    /**
     * @var array
     */
    protected $transformedColumns = array();

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param ManagerRegistry                $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer
    ) {
        $this->doctrine           = $doctrine;
        $this->propertyAccessor   = $propertyAccessor;
        $this->guesser            = $guesser;
        $this->colInfoTransformer = $colInfoTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($class, array $data, array $defaults = array())
    {
        $this->transformedColumns[$class] = array();
        $this->errors[$class]             = array();
        $entity                           = $this->getEntity($class, $data);
        $this->setDefaultValues($entity, $defaults);
        $this->setProperties($class, $entity, $data);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformedColumnsInfo($class)
    {
        return $this->transformedColumns[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors($class)
    {
        return $this->errors[$class];
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
            $columnInfo      = $this->colInfoTransformer->transform($class, $label);
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            $error           = $this->setProperty($entity, $columnInfo, $transformerInfo, $value);
            if ($error) {
                $this->errors[$class][$label] = array($error);
            }
        }
    }

    /**
     * Sets a property of the object
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
            if ($transformerInfo[0] instanceof EntityUpdaterInterface) {
                $transformerInfo[0]->setValue($entity, $columnInfo, $value, $transformerInfo[1]);
            } else {
                $this->propertyAccessor->setValue($entity, $columnInfo->getPropertyPath(), $value);
            }
        } catch (PropertyTransformerException $ex) {
            return array($ex->getMessageTemplate(), $ex->getMessageParameters());
        }

        $this->transformedColumns[get_class($entity)][] = $columnInfo;
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
                $this->doctrine->getManagerForClass($class)->getClassMetadata($class)
            );
            if (!$this->transformers[$class][$label]) {
                throw new UnknownColumnException(array($label), $class);
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
        $repository = $this->doctrine->getManagerForClass($class)->getRepository($class);

        $identifierProperties = $this->getEntityIdentifierProperties($repository);
        $identifier = $this->getEntityIdentifier($identifierProperties, $data);

        return $this->findOneByIdentifier($repository, $identifier);
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
        return new $class();
    }

    /**
     * @param array $identifierProperties
     * @param array $data
     *
     * @return string
     */
    protected function getEntityIdentifier(array $identifierProperties, array $data)
    {
        $identifier = implode(
            '.',
            array_map(
                function ($property) use ($data) {
                    if (!isset($data[$property])) {
                        throw new MissingIdentifierException();
                    }

                    return $data[$property];
                },
                $identifierProperties
            )
        );

        return $identifier;
    }

    /**
     * Transitional method that will be removed in 1.4
     *
     * @param $repository
     *
     * @return array
     *
     * @deprecated will be removed in 1.4
     */
    private function getEntityIdentifierProperties($repository)
    {
        if ($repository instanceof IdentifiableObjectRepositoryInterface) {
            return $repository->getIdentifierProperties();
        }

        if ($repository instanceof ReferableEntityRepositoryInterface) {
            return $repository->getReferenceProperties();
        }

        return [];
    }

    /**
     * Transitional method that will be removed in 1.4
     *
     * @param mixed  $repository
     * @param string $identifier
     *
     * @return mixed|null
     *
     * @deprecated will be removed in 1.4
     */
    private function findOneByIdentifier($repository, $identifier)
    {
        if ($repository instanceof IdentifiableObjectRepositoryInterface) {
            return $repository->findOneByIdentifier($identifier);
        }

        if ($repository instanceof ReferableEntityRepositoryInterface) {
            return $repository->findByReference($identifier);
        }

        return null;
    }
}
