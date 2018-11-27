<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Pim\Permission\Bundle\Filter\AttributeViewRightFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Attribute repository
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeRepository extends EntityRepository implements
    TranslatedLabelsProviderInterface,
    AttributeRepositoryInterface
{
    /** @var AttributeViewRightFilter */
    protected $attributeFilter;

    /**
     * @param EntityManager            $em
     * @param AttributeViewRightFilter $attributeFilter
     * @param string                   $classname
     */
    public function __construct(EntityManager $em, AttributeViewRightFilter $attributeFilter, $classname)
    {
        parent::__construct($em, $em->getClassMetadata($classname));

        $this->attributeFilter = $attributeFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function findTranslatedLabels(array $options = [])
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where($queryBuilder->expr()->eq('a.useableAsGridFilter', true));
        $query = $queryBuilder->getQuery();

        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeFilter->filterCollection(
            $query->execute(),
            'pim.internal_api.attribute.view'
        );

        $formattedAttributes = [];
        foreach ($attributes as $attribute) {
            $formattedAttributes[$attribute->getGroup()->getLabel()][$attribute->getLabel()] = $attribute->getCode();
        }

        return $formattedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributeCodesUsableInGrid($groupIds = null)
    {
        $qb = $this->createQueryBuilder('att')
            ->select('att.code');

        if (is_array($groupIds)) {
            if (empty($groupIds)) {
                return [];
            }

            $qb->andWhere('att.group IN (:groupIds)');
            $qb->setParameter('groupIds', $groupIds);
        }

        $qb->andWhere('att.useableAsGridFilter = :useableInGrid');
        $qb->setParameter('useableInGrid', 1);

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, 'code');
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributeCodes()
    {
        $qb = $this->createQueryBuilder('att')
            ->select('att.code');

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, 'code');
    }
}
