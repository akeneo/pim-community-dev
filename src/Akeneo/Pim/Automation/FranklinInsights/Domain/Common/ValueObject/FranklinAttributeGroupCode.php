<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class FranklinAttributeGroupCode
{
    private const CODE = 'franklin';

    private $code;

    public function __construct()
    {
        $this->code = self::CODE;
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}
