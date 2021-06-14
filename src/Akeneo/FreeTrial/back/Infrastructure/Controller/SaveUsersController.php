<?php


namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveUsersController
{
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([]);
    }
}