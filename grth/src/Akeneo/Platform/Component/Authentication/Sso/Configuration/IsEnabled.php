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
 * Is the SSO enabled or not?
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class IsEnabled
{
    /** @var bool */
    private $isEnabled;

    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function toBoolean(): bool
    {
        return $this->isEnabled;
    }
}
