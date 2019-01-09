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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;

/**
 * This command is a DTO holding and validating the raw values of a suggest data configuration.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateConnectionCommand
{
    /** @var Token */
    private $token;

    /**
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return Token
     */
    public function token()
    {
        return $this->token;
    }
}
