<?php

namespace Oro\Bundle\PimDataGridBundle\Repository;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
     */
    public function getDatagridViewTypeByUser(UserInterface $user): ArrayCollection;

    /**
     * Get all datagrid views by type
     *
     * @param UserInterface $user
     * @param string        $alias
     *
     *
     * @deprecated Please use DatagridViewRepositoryInterface::findDatagridViewBySearch instead
     */
    public function findDatagridViewByAlias(string $alias): ArrayCollection;

    /**
     * Search datagrid views for the given $user and grid $alias.
     * The search is applied on label with the given $term.
     * You can pass $options to add limit or page info.
     *
     * @param UserInterface $user
     * @param string        $alias
     * @param string        $term
     * @param array         $options
     */
    public function findDatagridViewBySearch(UserInterface $user, string $alias, string $term = '', array $options = []): ArrayCollection;
}
