<?php

namespace Oro\Bundle\FormBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\MappingException;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\FormException;

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
     * @return string
     * @throws FormException When entity has composite key
     */
    protected function getIdPropertyPathFromEntityManager(EntityManager $em, $className)
    {
        $meta = $em->getClassMetadata($className);
        try {
            return $meta->getSingleIdentifierFieldName();
        } catch (MappingException $e) {
            throw new FormException(
                "Cannot get id property path of entity. \"$this->className\" has composite primary key."
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

        return $this->propertyPath->getValue($value);
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
     * @return object
     * @throws UnexpectedTypeException if query builder callback returns invalid type
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
