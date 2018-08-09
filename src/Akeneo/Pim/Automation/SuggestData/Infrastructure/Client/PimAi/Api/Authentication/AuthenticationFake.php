<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication;

/**
 * Fake authentication
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AuthenticationFake implements AuthenticationApiInterface
{
    /** @var string */
    const VALID_TOKEN = 'valid-token';

    /**
     * {@inheritdoc}
     */
    public function authenticate(?string $token): bool
    {
        return $token === self::VALID_TOKEN;
    }
}
