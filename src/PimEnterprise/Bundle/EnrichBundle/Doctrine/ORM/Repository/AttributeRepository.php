<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use PimEnterprise\Bundle\CatalogBundle\Filter\AttributeViewRightFilter;

/**
 * Attribute repository
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeRepository extends EntityRepository implements TranslatedLabelsProviderInterface
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
            $formattedAttributes[$attribute->getGroup()->getLabel()][$attribute->getCode()] = $attribute->getLabel();
        }

        return $formattedAttributes;
    }
}
