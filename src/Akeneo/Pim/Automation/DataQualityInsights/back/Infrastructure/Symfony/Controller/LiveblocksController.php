<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LiveblocksController
{
    private const SECRET_KEY = 'sk_dev_jtZxlck8Ek8X8o2tRhoJSMvHR46p_tHFPvSWUohi890LCFUtWdW7bnLdfUPqprVP';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UserContext $userContext
    ) {
    }

    public function __invoke(string $roomId)
    {
        $user = $this->userContext->getUser();

        return $this->authorize($roomId, (string) $user->getId(), [
            'name' => $user->getFullName(),
            'picture' => $user->getAvatar() ? $user->getAvatar()->getKey() : null
        ]);
    }

    private function authorize(string $roomId, string $userId, array $userInfo): JsonResponse
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf('https://api.liveblocks.io/v2/rooms/%s/authorize', urlencode($roomId)),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::SECRET_KEY,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'userId' => $userId,
                    'userInfo' => $userInfo
                ]
            ]
        );

        return new JsonResponse($response->getContent(), $response->getStatusCode());
    }
}
