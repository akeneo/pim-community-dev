<?php

namespace Pim\Bundle\DataGridBundle\Repository;

use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Datagrid view repository interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatagridViewRepositoryInterface
{
    /**
     * Get all datagrid view type for a given user
     *
     * @param UserInterface $user
     *
     * @return ArrayCollection
     */
    public function getDatagridViewTypeByUser(UserInterface $user);

    /**
     * Get all datagrid views by user and type
     *
     * @param UserInterface $user
     * @param string        $alias
     */
    public function findDatagridViewByUserAndAlias(UserInterface $user, $alias);
}
