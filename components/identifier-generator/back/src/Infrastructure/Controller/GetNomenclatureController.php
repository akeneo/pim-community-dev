<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnexpectedAttributeTypeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $command = new GetNomenclatureCommand($propertyCode);

        try {
            return new JsonResponse(($this->getNomenclatureHandler)($command));
        } catch (UndefinedAttributeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (UnexpectedAttributeTypeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
