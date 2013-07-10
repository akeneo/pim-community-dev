<?php

namespace Oro\Bundle\TagBundle\Form;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms between array of entities and array of ids
 */
class TagsTransformer extends EntityToIdTransformer
{
    protected $tagManager;

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value || array() === $value) {
            return array();
        }

        if (!(is_array($value) || $value instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $result = array();
        foreach ($value as $entity) {
            $id = $this->propertyPath->getValue($entity);
            $result[] = $id;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($values)
    {
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        $newValues = array_filter(
            $values,
            function ($item) {
                return !intval($item);
            }
        );

        $values = array_filter(
            $values,
            function ($item) {
                return intval($item);
            }
        );

        $entities = array();
        if ($values) {
            $entities = $this->loadEntitiesByIds($values);
        }

        if ($newValues) {
            $entities = array_merge($entities, $this->tagManager->loadOrCreateTags($newValues));
        }

        if (count($entities) !== count($values) + count($newValues)) {
            throw new TransformationFailedException('Could not find all entities for the given IDs');
        }

        return $entities;
    }

    /**
     * Load entities by array of ids
     *
     * @param  array                   $ids
     * @return array
     * @throws UnexpectedTypeException if query builder callback returns invalid type
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

    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }
}
