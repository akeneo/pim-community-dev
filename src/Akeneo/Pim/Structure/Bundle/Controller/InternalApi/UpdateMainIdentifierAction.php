<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifier;
use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateMainIdentifierAction
{
    public function __construct(private readonly ChangeMainIdentifierHandler $handler)
    {
    }

    public function __invoke(Request $request, string $newIdentifierCode): Response
    {
        try {
            ($this->handler)(new ChangeMainIdentifier($newIdentifierCode));
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'ok' => false,
                    'message' => $e->getMessage(),
                ],
                400
            );
        }

        return new JsonResponse(['ok' => true]);
    }
}
