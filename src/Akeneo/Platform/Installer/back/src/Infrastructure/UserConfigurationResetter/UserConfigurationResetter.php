<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter;

use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;
use Webmozart\Assert\Assert;

class UserConfigurationResetter implements UserConfigurationResetterInterface
{
    /**
     * @param iterable<UserConfigurationResetterInterface> $userConfigurationResetters
     */
    public function __construct(private readonly iterable $userConfigurationResetters)
    {
        Assert::allIsInstanceOf($this->userConfigurationResetters, UserConfigurationResetterInterface::class);
    }

    public function execute(): void
    {
        foreach ($this->userConfigurationResetters as $userConfigurationResetter) {
            $userConfigurationResetter->execute();
        }
    }
}
