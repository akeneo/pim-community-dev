<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Association;

use PhpSpec\ObjectBehavior;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentAssociationsFilterSpec extends ObjectBehavior
{
    function it_filters_parent_associations()
    {
        $inputAssociations = [
            'PACK' => [
                'products' => [
                    'product_pack-1',
                    'product_pack-2',
                ],
                'product_models' => [
                    'product_model_pack-1',
                    'product_model_pack-2',
                    'product_model_pack-3',
                ],
                'groups' => [
                    'group_pack-1',
                    'group_pack-2',
                ],
            ],
        ];

        $parentAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    'product_model_pack-2',
                ],
                'groups' => [],
            ],
        ];

        $filteredAssociations = [
            'PACK' => [
                'products' => [
                    'product_pack-1',
                    'product_pack-2',
                ],
                'product_models' => [
                    'product_model_pack-1',
                    'product_model_pack-3',
                ],
                'groups' => [
                    'group_pack-1',
                    'group_pack-2',
                ],
            ],
        ];

        $this->filterParentAssociations($inputAssociations, $parentAssociations)
            ->shouldReturn($filteredAssociations);
    }
}
