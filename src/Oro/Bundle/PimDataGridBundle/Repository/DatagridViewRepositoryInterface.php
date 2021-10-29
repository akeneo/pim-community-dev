<?php

namespace Oro\Bundle\PimDataGridBundle\Repository;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

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
     * Get all datagrid view aliases for a given user.
     * Returns a list of aliases.
     */
    public function getDatagridViewAliasesByUser(UserInterface $user): array;

    /**
     * Search datagrid views for the given $user and grid $alias.
     * The search is applied on label with the given $term.
     * You can pass $options to add limit or page info.
     *
     * Returns a collection of DatagridView objects
     */
    public function findDatagridViewBySearch(
        UserInterface $user,
        string $alias,
        string $term = '',
        array $options = []
    ): array;

    public function findPublicDatagridViewByLabel(string $label): ?DatagridView;

    public function findPrivateDatagridViewByLabel(string $label, UserInterface $user): ?DatagridView;
}
