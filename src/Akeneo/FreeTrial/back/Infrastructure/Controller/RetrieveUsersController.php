<?php


namespace Akeneo\FreeTrial\Infrastructure\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RetrieveUsersController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            ['email' => 'test', 'status' => 'invited']
        ]);
    }
}