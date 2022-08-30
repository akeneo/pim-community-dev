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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class UserIntentRegistry
{
    /**
     * @param iterable<UserIntentFactoryInterface> $factories
     */
    public function __construct(private iterable $factories)
    {
    }

    public function getUserIntentFactory(TargetInterface $target, ValueInterface $value): UserIntentFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($target, $value)) {
                return $factory;
            }
        }

        throw new \InvalidArgumentException(sprintf('No factory found for target "%s"', $target->getCode()));
    }
}
