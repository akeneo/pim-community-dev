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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer\FamiliesNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class AttributeMappingController
{
    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingByFamilyHandler;

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var $familiesNormalizer */
    private $familiesNormalizer;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /** @var SaveAttributesMappingByFamilyHandler */
    private $updateAttributesMappingByFamilyHandler;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param SaveAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     * @param FamiliesNormalizer $familiesNormalizer
     * @param AttributesMappingNormalizer $attributesMappingNormalizer
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesNormalizer $familiesNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->updateAttributesMappingByFamilyHandler = $updateAttributesMappingByFamilyHandler;
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

        $query = new SearchFamiliesQuery($limit, $page, $request->get('search'));
        $families = $this->searchFamiliesHandler->handle($query);

        return new JsonResponse(
            $this->familiesNormalizer->normalize($families)
        );
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction(string $identifier): JsonResponse
    {
        $familyAttributesMapping = $this->getAttributesMappingByFamilyHandler->handle(
            new GetAttributesMappingByFamilyQuery($identifier)
        );

        return new JsonResponse([
            'code' => $identifier,
            'mapping' => $this->attributesMappingNormalizer->normalize($familyAttributesMapping),
        ]);
    }

    /**
     * @param string $identifier
     * @param Request $request
     *
     * @throws \Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Exception\InvalidMappingException
     *
     * @return Response
     */
    public function updateAction(string $identifier, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['mapping'])) {
            throw new BadRequestHttpException('No mapping have been sent');
        }

        $command = new SaveAttributesMappingByFamilyCommand($identifier, $data['mapping']);
        $this->updateAttributesMappingByFamilyHandler->handle($command);

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
