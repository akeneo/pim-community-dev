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
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($values)
    {
        $entities = array(
            'all'   => array(),
            'owner' => array()
        );

        if (!isset($values['all'], $values['owner'])) {
            return $values;
        }

        foreach (array_keys($entities) as $type) {
            if (!is_array($values[$type])) {
                $values[$type] = explode(',', $values[$type]);
            }

            $newValues[$type] = array_filter(
                $values[$type],
                function ($item) {
                    return !intval($item) && !empty($item);
                }
            );

            $values[$type] = array_filter(
                $values[$type],
                function ($item) {
                    return intval($item);
                }
            );

            if ($values[$type]) {
                $entities[$type] = $this->loadEntitiesByIds($values[$type]);
            }

            if ($newValues[$type]) {
                $entities[$type] = array_merge($entities[$type], $this->tagManager->loadOrCreateTags($newValues[$type]));
            }

            if (count($entities[$type]) !== count($values[$type]) + count($newValues[$type])) {
                throw new TransformationFailedException('Could not find all entities for the given IDs');
            }
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
