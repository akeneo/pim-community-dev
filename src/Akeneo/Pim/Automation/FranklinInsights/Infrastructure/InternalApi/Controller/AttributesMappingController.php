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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\FamiliesNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class AttributesMappingController
{
    use CheckAccessTrait;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingByFamilyHandler;

    /** @var SaveAttributesMappingByFamilyHandler */
    private $saveAttributesMappingByFamilyHandler;

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var FamiliesNormalizer */
    private $familiesNormalizer;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     * @param FamiliesNormalizer $familiesNormalizer
     * @param AttributesMappingNormalizer $attributesMappingNormalizer
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesNormalizer $familiesNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        SecurityFacade $securityFacade
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->saveAttributesMappingByFamilyHandler = $saveAttributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;
        $this->familiesNormalizer = $familiesNormalizer;
        $this->attributesMappingNormalizer = $attributesMappingNormalizer;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->checkAccess('akeneo_franklin_insights_settings_mapping');
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
        $this->checkAccess('akeneo_franklin_insights_settings_mapping');
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
     * @throws \Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException
     *
     * @return Response
     */
    public function updateAction(string $identifier, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $this->checkAccess('akeneo_franklin_insights_settings_mapping');

        $data = json_decode($request->getContent(), true);

        if (!isset($data['mapping'])) {
            throw new BadRequestHttpException('No mapping have been sent');
        }

        try {
            $command = new SaveAttributesMappingByFamilyCommand($identifier, $data['mapping']);
            $this->saveAttributesMappingByFamilyHandler->handle($command);
        } catch (AttributeMappingException | DataProviderException $e) {
            return new JsonResponse([[$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($data);
    }
}
