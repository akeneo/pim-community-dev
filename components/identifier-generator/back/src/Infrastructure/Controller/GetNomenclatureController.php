<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureQuery;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNomenclatureController
{
    public function __construct(
        private readonly GetNomenclatureHandler $getNomenclatureHandler,
    ) {
    }

    public function __invoke(Request $request, string $propertyCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $query = new GetNomenclatureQuery($propertyCode);

        return new JsonResponse(($this->getNomenclatureHandler)($query));
    }
}
