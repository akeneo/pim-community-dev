<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository as BaseRoleRepository;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Role repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleRepository extends BaseRoleRepository implements ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(array('label' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('label');
    }
}
