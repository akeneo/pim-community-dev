<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

interface SelectCategoryCodesByProductGridFiltersQueryInterface
{
    /**
     * The format of the filters is those of the product-grid
     * Example :
     * [
     *     'field'    => 'categories',
     *     'operator' => 'IN CHILDREN',
     *     'type'     => 'field',
     *     'value'    => [
     *         'tvs_projectors'
     *     ]
     * ]
     */
    public function execute(array $filters): array;
}
