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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory;

use _PHPStan_76800bfb5\Symfony\Component\Console\Exception\LogicException;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;

final class MultiSelectUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param AttributeTarget $target
     */
    public function create(TargetInterface $target, string $value): ValueUserIntent
    {
        if (!$this->supports($target)) {
            throw new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_multiselect"');
        }

        return match ($target->getActionIfNotEmpty()) {
            TargetInterface::ACTION_ADD => new AddMultiSelectValue(
                $target->getCode(),
                $target->getChannel(),
                $target->getLocale(),
                [$value],
            ),
            TargetInterface::ACTION_SET => new SetMultiSelectValue(
                $target->getCode(),
                $target->getChannel(),
                $target->getLocale(),
                [$value],
            ),
            default => throw new LogicException(),
        };
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof AttributeTarget && 'pim_catalog_multiselect' === $target->getType();
    }
}
