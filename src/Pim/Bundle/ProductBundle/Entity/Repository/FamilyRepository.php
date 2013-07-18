<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\ProductBundle\Doctrine\EntityRepository;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllWithTranslations()
    {
        return $this->build()->addSelect('translation')->leftJoin('family.translations', 'translation');
    }

    /**
     * Find all families ordered by label with fallback to default mecanism
     *
     * @return array
     */
    public function getIdToLabelOrderedByLabel()
    {
        $families = $this->buildAllWithTranslations()->getQuery()->execute();
        $orderedFamilies = array();
        foreach ($families as $family) {
            $orderedFamilies[$family->getId()]= $family->getLabel();
        }
        uasort(
            $orderedFamilies,
            function ($first, $second) {
                return ($first === $second) ? 0 : strcasecmp($first, $second);
            }
        );

        return $orderedFamilies;
    }

    /**
     * @param integer $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildOneWithAttributes($id)
    {
        return $this
            ->buildOne($id)
            ->addSelect('attribute')
            ->leftJoin('family.attributes', 'attribute')
            ->leftJoin('attribute.group', 'group')
            ->addOrderBy('group.sortOrder', 'ASC')
            ->addOrderBy('attribute.sortOrder', 'ASC');
    }
}
