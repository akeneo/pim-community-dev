<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\FamilyTemplate\Infrastructure\Controller;

use Akeneo\Pim\Structure\FamilyTemplate\Infrastructure\Query\FetchFamilyTemplates;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetFamilyTemplatesAction
{
    public function __construct(
        private readonly FetchFamilyTemplates $fetchFamilyTemplates
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $familyTemplates = $this->fetchFamilyTemplates->all();

        return new JsonResponse($familyTemplates);
    }
}
