<?php

namespace Pim\Bundle\UserBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\Repository\GroupRepository as BaseGroupRepository;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * User group repository
 *
 * @author    Julien Janvier <julien.janvier@gmail.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends BaseGroupRepository implements ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(array('name' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('name');
    }
}
