<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RedeemCodeForToken implements RedeemCodeForTokenInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function redeem(AccessTokenRequest $accessTokenRequest): string
    {
        $this->validateAccessToken($accessTokenRequest);

        return 'a_random_token';
    }

    private function validateAccessToken(AccessTokenRequest $accessTokenRequest): void
    {
        $violations = $this->validator->validate($accessTokenRequest);
        if ($violations->count() > 0) {
            throw new \InvalidArgumentException($violations[0]->getMessage());
        }
    }
}
