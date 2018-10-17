<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms between array of entities and array of ids
 */
class EntitiesToIdsTransformer extends EntityToIdTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value || [] === $value) {
            return [];
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $result = [];
        foreach ($value as $entity) {
            $id = $this->propertyAccessor->getValue($entity, $this->propertyPath);
            $result[] = $id;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (!$value) {
            return [];
        }

        $entities = $this->loadEntitiesByIds($value);

        if (count($entities) !== count($value)) {
            throw new TransformationFailedException('Could not find all entities for the given IDs');
        }

        return $entities;
    }

    /**
     * Load entities by array of ids
     *
     * @param array $ids
     * @throws UnexpectedTypeException if query builder callback returns invalid type
     * @return array
     */
    protected function loadEntitiesByIds(array $ids)
    {
        $repository = $this->em->getRepository($this->className);
        if ($this->queryBuilderCallback) {
            /** @var $qb QueryBuilder */
            $qb = call_user_func($this->queryBuilderCallback, $repository, $ids);
            if (!$qb instanceof QueryBuilder) {
                throw new UnexpectedTypeException($qb, 'Doctrine\ORM\QueryBuilder');
            }
        } else {
            $qb = $repository->createQueryBuilder('e');
            $qb->where(sprintf('e.%s IN (:ids)', $this->propertyPath))
                ->setParameter('ids', $ids);
        }

        return $qb->getQuery()->execute();
    }
}
