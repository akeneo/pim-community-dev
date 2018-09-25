<?php

/*
 * TODO DELETE
 *
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// TODO To delete

namespace Akeneo\Pim\Permission\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\ProductGridFilterChoiceType as BaseProductGridFilterChoiceType;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductGridFilterChoiceType extends BaseProductGridFilterChoiceType
{
    /**
     * Permissions filter is not configured in yaml file but by event.
     * There is not way to get it without booting the grid.
     *
     * {@inheritdoc}
     */
    protected function getSystemFilters()
    {
        $filters = parent::getSystemFilters();

        $filters['System'] = $filters['System'] + [
            'pim_common.permissions' => 'permissions',
        ];

        return $filters;
    }
}
