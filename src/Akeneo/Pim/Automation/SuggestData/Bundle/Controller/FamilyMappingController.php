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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class FamilyMappingController
{
    /**
     * TODO Move this into the model.
     */
    private const PENDING = 0;
    private const ACTIVE = 1;
    private const INACTIVE = 2;

    /**
     * Mocked return
     * TODO Make it for real!
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return new Response(json_encode([
            [
                'family' => 'clothing',
                'enabled' => true,
                'mapping' => []
            ], [
                'family' => 'accessories',
                'enabled' => false,
                'mapping' => []
            ], [
                'family' => 'camcorders',
                'enabled' => true,
                'mapping' => [
                    [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 1'
                        ],
                        'attribute' => [ // TODO Use the standard attribute normalizer for this.
                            'code' => 'weight',
                            'labels' => [
                                'en_US' => 'Weight',
                                'fr_FR' => 'Hauteur',
                                'de_DE' => 'Auf wiedersehen'
                            ]
                        ],
                        'status' => self::ACTIVE
                    ], [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 2'
                        ],
                        'attribute' => null,
                        'status' => self::PENDING
                    ], [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 2'
                        ],
                        'attribute' => null,
                        'status' => self::INACTIVE
                    ]
                ]
            ]
        ]));
    }
}
