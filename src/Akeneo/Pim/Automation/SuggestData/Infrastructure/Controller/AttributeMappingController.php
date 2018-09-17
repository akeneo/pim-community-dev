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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi\AttributesMappingNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttributeMappingController
{
    /** TODO Move this into the model. */

    /** There is no attributes to map (i.e. it has no attributes) */
    private const MAPPING_EMPTY = 0;

    /** All attributes are mapped (i.e. it has attributes and no pending attributes) */
    private const MAPPING_FULL = 1;

    /** There is new attributes to map (i.e. it has at least 1 pending attribute) */
    private const MAPPING_PENDING_ATTRIBUTES = 2;

    /** @var GetAttributesMappingByFamilyHandler */
    private $attributesMappingByFamilyHandler;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /**
     * @param GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler
     * @param AttributesMappingNormalizer         $attributesMappingNormalizer
     */
    public function __construct(GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler, AttributesMappingNormalizer $attributesMappingNormalizer)
    {
        $this->attributesMappingByFamilyHandler = $attributesMappingByFamilyHandler;
        $this->attributesMappingNormalizer = $attributesMappingNormalizer;
    }

    /**
     * Mocked return
     * TODO Make it for real:
     * Should return all the families of the PIM, with enabled at false for non existing familyMapping entities
     *
     * @return Response
     */
    public function listAction(Request $request): JsonResponse
    {
        $RESPONSE = [
            [
                'code' => 'clothing',
                'status' => self::MAPPING_EMPTY,
                'labels' => [
                    'en_US' => 'clothing',
                    'fr_FR' => 'vetements',
                    'de_DE' => 'Kartoffeln'
                ]
            ], [
                'code' => 'accessories',
                'status' => self::MAPPING_PENDING_ATTRIBUTES,
                'labels' => [
                    'en_US' => 'accessories',
                    'fr_FR' => 'accessoires',
                    'de_DE' => 'Shön'
                ]
            ], [
                'code' => 'camcorders',
                'status' => self::MAPPING_FULL,
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
    public function getAction(string $identifier): JsonResponse
    {
        $familyAttributesMapping = $this->attributesMappingByFamilyHandler->handle(
            new GetAttributesMappingByFamilyQuery($identifier)
        );

        return new JsonResponse([
            'code' => $identifier,
            'mapping' => $this->attributesMappingNormalizer->normalize($familyAttributesMapping),
        ]);
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
