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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi\FamiliesNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttributeMappingController
{
    /** @var GetAttributesMappingByFamilyHandler */
    private $attributesMappingByFamilyHandler;

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var $familiesNormalizer */
    private $familiesNormalizer;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /**
     * @param GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     * @param FamiliesNormalizer $familiesNormalizer
     * @param AttributesMappingNormalizer $attributesMappingNormalizer
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesNormalizer $familiesNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer
    ) {
        $this->attributesMappingByFamilyHandler = $attributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;
        $this->familiesNormalizer = $familiesNormalizer;
        $this->attributesMappingNormalizer = $attributesMappingNormalizer;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $options = $request->get('options', []);

        $limit = 20;
        if (isset($options['limit'])) {
            $limit = (int) $options['limit'];
        }

        $page = 1;
        if (isset($options['page'])) {
            $page = (int) $options['page'];
        }

        $identifiers = [];
        if (isset($options['identifiers'])) {
            $identifiers = $options['identifiers'];
        }

        $query = new SearchFamiliesQuery($limit, $page, $identifiers, $request->get('search'));
        $families = $this->searchFamiliesHandler->handle($query);

        return new JsonResponse(
            $this->familiesNormalizer->normalize($families)
        );
    }

    /**
     * @param string   $identifier
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
