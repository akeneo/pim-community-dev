<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Query;

use Akeneo\UserManagement\Component\Model\User;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListAttributesQuery
{
    /**
     * Fetch a paginated list of attributes for the product grid
     *
     * @param string      $locale        Code of the locale for the translation of the labels
     * @param int         $page          Number of the page (start at 1)
     * @param string|null $searchOnLabel String to search in the attribute label
     * @param User|null   $user          Context's user if needed
     *
     * @return array
     */
    public function fetch(string $locale, int $page, string $searchOnLabel = '', User $user = null): array;
}
