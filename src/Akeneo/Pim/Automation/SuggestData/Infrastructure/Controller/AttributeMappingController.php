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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttributeMappingController
{
    /**
     * TODO Move this into the model.
     */
    private const PENDING = 0;
    private const ACTIVE = 1;
    private const INACTIVE = 2;

    /**
     * @param string   $identifier
     * @param Response $response
     *
     * @return JsonResponse
     */
    public function getAction($identifier, Request $request): JsonResponse
    {
        if ('camcorders' === $identifier) {
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
        } elseif ('clothing' === $identifier) {
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
