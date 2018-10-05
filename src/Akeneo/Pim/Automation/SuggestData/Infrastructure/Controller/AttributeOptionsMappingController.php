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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMappingController
{
    /**
     * TODO Unmock data
     *
     * @return JsonResponse
     */
    public function getAction(): JsonResponse
    {
        return new JsonResponse([
            'family' => 'router',
            'pim_ai_attribute' => 'color',
            'mapping' => [
                'color_1' => [
                    'pim_ai_attribute_option_code' => [
                        'label' => 'Color 1',
                    ],
                    'attribute_option' => null,
                    'status' => 0
                ],
                'color_2' => [
                    'pim_ai_attribute_option_code' => [
                        'label' => 'Color 2',
                    ],
                    'attribute_option' => 'color2',
                    'status' => 1
                ],
                'color_3' => [
                    'pim_ai_attribute_option_code' => [
                        'label' => 'Color 3',
                    ],
                    'attribute_option' => null,
                    'status' => 2
                ],
            ]
        ]);
    }

    /**
     * TODO Unmock data
     *
     * @return JsonResponse
     */
    public function updateAction()
    {
        sleep(1);
        
        return new JsonResponse(['response' => 'It\'s a temporary OK!']);
    }
}
