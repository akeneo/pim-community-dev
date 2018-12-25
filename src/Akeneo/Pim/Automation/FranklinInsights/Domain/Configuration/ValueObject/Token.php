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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class Token
{
    /** @var string */
    private $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        if (empty($token)) {
            throw new \InvalidArgumentException('Token must be a string');
        }
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->token;
    }
}
