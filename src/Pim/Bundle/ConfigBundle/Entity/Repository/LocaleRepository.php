<?php
namespace Pim\Bundle\ConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleRepository extends EntityRepository
{

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('code' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('code' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Find locales that have a fallback locale defined
     *
     * @return array
     */
    public function findWithFallback()
    {
        return $this->createQueryBuilder('l')
            ->where('l.fallback IS NOT NULL')->orderBy('l.code')->getQuery()->getResult();
    }

    /**
     * Return a query builder for activated locales
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getActivatedLocales()
    {
        return $this->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
    }
}
