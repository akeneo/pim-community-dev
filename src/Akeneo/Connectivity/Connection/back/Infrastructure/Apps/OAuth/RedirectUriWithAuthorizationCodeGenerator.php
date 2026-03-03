<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RedirectUriWithAuthorizationCodeGenerator implements RedirectUriWithAuthorizationCodeGeneratorInterface
{
    public function __construct(private AuthorizationCodeGeneratorInterface $authorizationCodeGenerator)
    {
    }

    public function generate(
        AppAuthorization $appAuthorization,
        AppConfirmation $appConfirmation,
        int $pimUserId
    ): string {
        $redirectUri = $appAuthorization->getRedirectUri();

        $code = $this->authorizationCodeGenerator->generate(
            $appConfirmation,
            $pimUserId,
            $redirectUri
        );
        $state = $appAuthorization->getState();

        return $this->appendQueryParams($redirectUri, [
            'code' => $code,
            'state' => $state,
        ]);
    }

    private function appendQueryParams(string $url, array $parameters): string
    {
        $query = \http_build_query($parameters);

        return \parse_url($url, PHP_URL_QUERY) ? \sprintf('%s&%s', $url, $query) : \sprintf('%s?%s', $url, $query);
    }
}
