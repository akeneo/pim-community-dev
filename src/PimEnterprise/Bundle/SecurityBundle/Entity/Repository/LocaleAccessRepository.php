<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Locale access repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleAccessRepository extends EntityRepository
{
    /**
     * Get roles that have the specified access to a locale
     *
     * @param Locale $locale
     * @param string $accessLevel
     *
     * @return Role[]
     */
    public function getGrantedRoles(Locale $locale, $accessLevel)
    {
        $accessField = $this->getAccessField($accessLevel);
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.locale = :locale')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a locales
     * If excluded roles are provided, access will not be revoked for these roles
     *
     * @param Locale locale
     * @param Role[] $excludedRoles
     *
     * @return integer
     */
    public function revokeAccess(Locale $locale, array $excludedRoles = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.locale = :locale')
            ->setParameter('locale', $locale);

        if (!empty($excludedRoles)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.role', ':excludedRoles'))
                ->setParameter('excludedRoles', $excludedRoles);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
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
