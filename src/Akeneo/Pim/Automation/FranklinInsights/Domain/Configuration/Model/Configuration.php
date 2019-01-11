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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;

/**
 * Holds the configuration with the token.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class Configuration
{
    /** @var string */
    private $token;

    /**
     * @return Token|null
     */
    public function getToken(): ?Token
    {
        return (null === $this->token) ? null : new Token($this->token);
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token): void
    {
        $this->token = (string) $token;
    }
}
