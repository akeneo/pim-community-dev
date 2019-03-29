<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AddDraftStatusFilterToProductGridListener
{
    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if ('product-grid' !== $datagridConfiguration->getName()) {
            return;
        }

        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        $filters['columns']['draft_status'] = [
            'type' => 'draft_status',
            'ftype' => 'choice',
            'label' => 'Draft status',
            'data_name' => 'draft_status',
            'options' => [
                'field_options' => [
                    'choices' => [
                        'Waiting for approval' => 2,
                        'In Progress' => 1,
                        'Working copy' => 0,
                    ]
                ]
            ]
        ];
        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);

    }
}
