<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Doctrine\EntityRepository;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRepository extends EntityRepository
{
    /**
     * @param $localeCode the locale to use for name translation
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAllOrderedByName($localeCode)
    {
        $build = $this->build()
        /*
         * TODO
            ->addSelect('translation')
            ->leftJoin('attribute_group.translations', 'translation', 'with', 'translation.locale = :locale')
            ->setParameter('locale', $localeCode)
            ->orderBy('translation.name');*/
            ->addOrderBy('attribute_group.code')
        ;

        return $build;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllOrderedBySortOrder()
    {
        return $this->build()->orderBy('attribute_group.sortOrder');
    }
}
