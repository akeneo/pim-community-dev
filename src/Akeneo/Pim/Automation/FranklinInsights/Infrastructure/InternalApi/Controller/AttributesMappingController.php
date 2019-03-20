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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\FamiliesMappingStatusNormalizer;
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

    /** @var FamiliesMappingStatusNormalizer */
    private $familiesNormalizer;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /**
     * @param GetAttributesMappingByFamilyHandler  $getAttributesMappingByFamilyHandler
     * @param SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler                $searchFamiliesHandler
     * @param FamiliesMappingStatusNormalizer      $familiesNormalizer
     * @param AttributesMappingNormalizer          $attributesMappingNormalizer
     * @param SecurityFacade                       $securityFacade
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FamiliesMappingStatusNormalizer $familiesNormalizer,
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
            new GetAttributesMappingByFamilyQuery(new FamilyCode($identifier))
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

        $familyCode = new FamilyCode($identifier);
        $command = new SaveAttributesMappingByFamilyCommand($familyCode, $data['mapping']);
        $this->saveAttributesMappingByFamilyHandler->handle($command);

        return new JsonResponse($data);
    }
}
