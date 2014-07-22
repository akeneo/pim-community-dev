<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Locale access repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleAccessRepository extends EntityRepository
{
}
