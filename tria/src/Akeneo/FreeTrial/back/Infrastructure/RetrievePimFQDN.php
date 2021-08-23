<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure;

class RetrievePimFQDN
{
    private string $fqdn;

    public function __construct(string $fqdn)
    {
        $this->fqdn = $fqdn;
    }

    public function __invoke(): string
    {
        return $this->fqdn;
    }
}
