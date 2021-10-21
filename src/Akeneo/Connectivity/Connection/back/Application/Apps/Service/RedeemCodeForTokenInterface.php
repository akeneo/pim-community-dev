<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RedeemCodeForTokenInterface
{
    /**
     * @param AccessTokenRequest $accessTokenRequest
     *
     * @throws \InvalidArgumentException if the AccessTokenRequest is not valid
     *
     * @return string
     */
    public function redeem(AccessTokenRequest $accessTokenRequest): string;
}
