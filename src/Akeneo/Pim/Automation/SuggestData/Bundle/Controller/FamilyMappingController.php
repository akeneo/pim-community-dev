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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * TODO Make it for real:
     * Should return all the families of the PIM, with enabled at false for non existing familyMapping entities
     *
     * @return Response
     */
    public function indexAction(Request $request): JsonResponse
    {
        $RESPONSE = [
            [
                'code' => 'clothing',
                'enabled' => true,
                'labels' => [
                    'en_US' => 'clothing',
                    'fr_FR' => 'vetements',
                    'de_DE' => 'Kartoffeln'
                ]
            ], [
                'code' => 'accessories',
                'enabled' => false,
                'labels' => [
                    'en_US' => 'accessories',
                    'fr_FR' => 'accessoires',
                    'de_DE' => 'Shön'
                ]
            ], [
                'code' => 'camcorders',
                'enabled' => true,
                'labels' => [
                    'en_US' => 'camcorders',
                    'fr_FR' => 'caméras',
                    'de_DE' => 'Mein Fuss tut weh'
                ]
            ]
        ];
        
        /** non treated arguments:
         * options[limit]: 20
         * options[page]: 1
         * options[catalogLocale]: en_US (useless, comes from select2)
         */

        if (null !== $request->get('search') && '' !== $request->get('search')) {
            return new JsonResponse(array_filter($RESPONSE, function ($family) use ($request) {
                return strpos($family['code'], $request->get('search')) !== false;
            }));
        }


        if (null !== $request->get('options') && isset($request->get('options')['identifiers'])) {
            return new JsonResponse(array_filter($RESPONSE, function ($family) use ($request) {
                return in_array($family['code'], $request->get('options')['identifiers']);
            }));
        }

        return new JsonResponse($RESPONSE);
    }

    public function getAction(): JsonResponse
    {
        return new JsonResponse([
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
        ]);
    }
}
