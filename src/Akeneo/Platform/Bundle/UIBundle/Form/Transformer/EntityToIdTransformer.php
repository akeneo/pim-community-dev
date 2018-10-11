<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Platform\Bundle\UIBundle\Form\Exception\FormException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Transforms between entity and id
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var PropertyPath
     */
    protected $propertyPath;

    /**
     * @var callable
     */
    protected $queryBuilderCallback;

    /**
     * @param EntityManager $em
     * @param string $className
     * @param string|null $property
     * @param callable $queryBuilderCallback
     * @throws UnexpectedTypeException When $queryBuilderCallback is set and not callable
     */
    public function __construct(EntityManager $em, $className, $property = null, $queryBuilderCallback = null)
    {
        $this->em = $em;
        $this->className = $className;
        if (!$property) {
            $property = $this->getIdPropertyPathFromEntityManager($em, $className);
        }
        $this->property = $property;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->propertyPath = new PropertyPath($this->property);
        if (null !== $queryBuilderCallback && !is_callable($queryBuilderCallback)) {
            throw new UnexpectedTypeException($queryBuilderCallback, 'callable');
        }
        $this->queryBuilderCallback = $queryBuilderCallback;
    }

    /**
     * Get identifier field name of entity using metadata
     *
     * @param EntityManager $em
     * @param string $className
     * @throws FormException When entity has composite key
     * @return string
     */
    protected function getIdPropertyPathFromEntityManager(EntityManager $em, $className)
    {
        $meta = $em->getClassMetadata($className);
        try {
            return $meta->getSingleIdentifierFieldName();
        } catch (MappingException $e) {
            throw new FormException(
                "Cannot get id property path of entity. \"$className\" has composite primary key."
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        return $this->propertyAccessor->getValue($value, $this->propertyPath);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        return $this->loadEntityById($value);
    }

    /**
     * Load entity by id
     *
     * @param mixed $id
     * @throws UnexpectedTypeException if query builder callback returns invalid type
     * @return object
     */
    protected function loadEntityById($id)
    {
        $repository = $this->em->getRepository($this->className);
        if ($this->queryBuilderCallback) {
            /** @var $qb QueryBuilder */
            $qb = call_user_func($this->queryBuilderCallback, $repository, $id);
            if (!$qb instanceof QueryBuilder) {
                throw new UnexpectedTypeException($qb, 'Doctrine\ORM\QueryBuilder');
            }
            return $qb->getQuery()->execute();
        } else {
            return $repository->find($id);
        }
    }
}
