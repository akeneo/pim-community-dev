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

    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" is not a valid url', $url));
        }

        $this->fqdn = parse_url($url, PHP_URL_HOST);
    }

    public function __invoke(): string
    {
        return $this->fqdn;
    }
}
