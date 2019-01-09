<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface AuthenticationProviderInterface
{
    /**
     * @param Token $token
     *
     * @return bool
     */
    public function authenticate(Token $token): bool;
}
