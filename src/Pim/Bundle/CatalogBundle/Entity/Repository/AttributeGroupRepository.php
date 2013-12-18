<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRepository extends UniqueCodeEntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllWithTranslations()
    {
        return $this->build()->addSelect('translation')->leftJoin('attribute_group.translations', 'translation');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAllOrderedBySortOrder()
    {
        return $this->build()->orderBy('attribute_group.sortOrder');
    }

    /**
     * Find all ordered by label with fallback to default mecanism
     *
     * @return array
     */
    public function getIdToLabelOrderedBySortOrder()
    {
        $groups = $this->buildAllOrderedBySortOrder()->getQuery()->execute();
        $orderedGroups = array();
        foreach ($groups as $group) {
            $orderedGroups[$group->getId()]= $group->getLabel();
        }

        return $orderedGroups;
    }
}
