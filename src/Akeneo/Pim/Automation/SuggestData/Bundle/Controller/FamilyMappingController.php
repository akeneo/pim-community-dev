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
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * @param string   $identifier
     * @param Response $response
     *
     * @return JsonResponse
     */
    public function getAction($identifier, Request $request): JsonResponse
    {
        if ($identifier === 'camcorders') {
            return new JsonResponse([
                'code' => 'camcorders',
                'enabled' => true,
                'mapping' => [
                    'pimaiattributecode1' => [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 1'
                        ],
                        'attribute' => 'weight',
                        'status' => self::ACTIVE
                    ],
                    'pimaiattributecode2' => [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 2'
                        ],
                        'attribute' => null,
                        'status' => self::PENDING
                    ],
                    'pimaiattributecode3' => [
                        'pim_ai_attribute' => [
                            'label' => 'the pim.ai attribute label 3'
                        ],
                        'attribute' => null,
                        'status' => self::INACTIVE
                    ]
                ]
            ]);
        } else if ($identifier === 'clothing') {
            return new JsonResponse([
                'code' => 'clothing',
                'enabled' => true,
                'mapping' => []
            ]);
        } else {
            return new JsonResponse([
                'code' => 'accessories',
                'enabled' => false,
                'mapping' => []
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        /*
        $familyMapping = $this->getOrCreateFamilyMapping($data['code'])
        $this->updater->update($familyMapping, $data);

        $violations = $this->validator->validate($familyMapping);
        if (0 < $violations->count()) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }

            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }
        $this->saver->save($familyMapping);

        return new JsonResponse($this->normalizer->normalize($familyMapping, 'internal_api'));
        */

        // TODO Temporary return, always valid.
        return new JsonResponse($data);
    }
}
