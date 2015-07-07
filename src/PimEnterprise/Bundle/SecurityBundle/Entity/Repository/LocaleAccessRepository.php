<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Locale access repository
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleAccessRepository extends EntityRepository
{
    /**
     * Get group that have the specified access to a locale
     *
     * @param LocaleInterface $locale
     * @param string          $accessLevel
     *
     * @return Group[]
     */
    public function getGrantedUserGroups(LocaleInterface $locale, $accessLevel)
    {
        $accessField = $this->getAccessField($accessLevel);
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('g')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->where('a.locale = :locale')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a locales
     * If excluded user groups are provided, access will not be revoked for these groups
     *
     * @param LocaleInterface $locale
     * @param array           $excludedUserGroups
     *
     * @return mixed
     */
    public function revokeAccess(LocaleInterface $locale, array $excludedUserGroups = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.locale = :locale')
            ->setParameter('locale', $locale);

        if (!empty($excludedUserGroups)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.userGroup', ':excludedUserGroups'))
                ->setParameter('excludedUserGroups', $excludedUserGroups);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getAccessField($accessLevel)
    {
        $mapping = [
            Attributes::EDIT_PRODUCTS => 'editProducts',
            Attributes::VIEW_PRODUCTS => 'viewProducts'
        ];
        if (!isset($mapping[$accessLevel])) {
            throw new \LogicException(sprintf('%s access level not exists', $accessLevel));
        }

        return $mapping[$accessLevel];
    }
}
