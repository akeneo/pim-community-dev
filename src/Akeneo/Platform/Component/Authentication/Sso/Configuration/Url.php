<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Represents a URL value in the configuration. The URL must have a valid format.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Url
{
    /** @var string */
    private $url;

    private function __construct(string $url)
    {
        $this->url = $url;
    }

    public static function fromString(string $url): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Value must be a valid url, "%s" given.', $url));
        }

        return new self($url);
    }
}
