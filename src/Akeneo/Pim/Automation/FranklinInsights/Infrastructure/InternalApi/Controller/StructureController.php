<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\AddAttributeToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\AddAttributeToFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkAddAttributesToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkAddAttributesToFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkCreateAttributesInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkCreateAttributesInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToAddToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToCreate;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StructureController
{
    /** @var SecurityFacade */
    private $securityFacade;

    /** @var CreateAttributeInFamilyHandler */
    private $createAttributeInFamilyHandler;

    /** @var AddAttributeToFamilyHandler */
    private $addAttributeToFamilyHandler;

    /** @var BulkCreateAttributesInFamilyHandler */
    private $bulkCreateAttributesInFamilyHandler;

    /** @var BulkAddAttributesToFamilyHandler */
    private $bulkAddAttributesToFamilyHandler;

    public function __construct(
        SecurityFacade $securityFacade,
        CreateAttributeInFamilyHandler $createAttributeInFamilyHandler,
        AddAttributeToFamilyHandler $addAttributeToFamilyHandler,
        BulkCreateAttributesInFamilyHandler $bulkCreateAttributesInFamilyHandler,
        BulkAddAttributesToFamilyHandler $bulkAddAttributesToFamilyHandler
    ) {
        $this->securityFacade = $securityFacade;
        $this->createAttributeInFamilyHandler = $createAttributeInFamilyHandler;
        $this->addAttributeToFamilyHandler = $addAttributeToFamilyHandler;
        $this->bulkCreateAttributesInFamilyHandler = $bulkCreateAttributesInFamilyHandler;
        $this->bulkAddAttributesToFamilyHandler = $bulkAddAttributesToFamilyHandler;
    }

    public function createAttributeAction(Request $request): Response
    {
        if (false === $request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (false === $this->isUserAllowedToCreate()) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        $pimAttributeCode = AttributeCode::fromLabel($data['franklinAttributeLabel']);
        $command = new CreateAttributeInFamilyCommand(
            new FamilyCode($data['familyCode']),
            $pimAttributeCode,
            new FranklinAttributeLabel($data['franklinAttributeLabel']),
            new FranklinAttributeType($data['franklinAttributeType'])
        );
        $this->createAttributeInFamilyHandler->handle($command);

        return new JsonResponse(['code' => (string)$pimAttributeCode]);
    }

    public function addAttributeToFamilyAction(Request $request): Response
    {
        if (false === $request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (false === $this->isUserAllowedToAttachToFamily()) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        $pimAttributeCode = new AttributeCode($data['attributeCode']);
        $command = new AddAttributeToFamilyCommand($pimAttributeCode, new FamilyCode($data['familyCode']));
        $this->addAttributeToFamilyHandler->handle($command);

        return new JsonResponse([
            'code' => (string) $pimAttributeCode
        ]);
    }

    public function bulkCreateAttributesAction(string $familyCode, Request $request): JsonResponse
    {
        if (false === $this->isUserAllowedToCreate()) {
            throw new AccessDeniedException();
        }

        $attributes = json_decode($request->getContent(), true);

        $command = new BulkCreateAttributesInFamilyCommand(new FamilyCode($familyCode), new AttributesToCreate($attributes));
        $this->bulkCreateAttributesInFamilyHandler->handle($command);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    public function bulkAddAttributesToFamilyAction(string $familyCode, Request $request): JsonResponse
    {
        if (false === $this->isUserAllowedToCreate()) {
            throw new AccessDeniedException();
        }

        $attributeCodes = json_decode($request->getContent(), true);
        $attributeCodes = array_map(function (string $attributeCode) {
            return new AttributeCode($attributeCode);
        }, $attributeCodes);

        $command = new BulkAddAttributesToFamilyCommand(new FamilyCode($familyCode), $attributeCodes);
        $this->bulkAddAttributesToFamilyHandler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToCreate(): bool
    {
        return $this->securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')
            && $this->securityFacade->isGranted('pim_enrich_attribute_create')
            && $this->securityFacade->isGranted('pim_enrich_family_edit_attributes');
    }

    private function isUserAllowedToAttachToFamily(): bool
    {
        return $this->securityFacade->isGranted('akeneo_franklin_insights_settings_mapping')
            && $this->securityFacade->isGranted('pim_enrich_family_edit_attributes');
    }
}
