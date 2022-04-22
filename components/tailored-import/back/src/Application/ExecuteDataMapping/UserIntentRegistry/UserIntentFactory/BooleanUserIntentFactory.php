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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;

final class BooleanUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param AttributeTarget $target
     */
    public function create(TargetInterface $target, string|array $value): ValueUserIntent
    {
        if (!$this->supports($target)) {
            throw new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_boolean"');
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException('BooleanUserIntentFactory only supports string value');
        }

        if (!\in_array($value, ['1', '0'], true)) {
            throw new \InvalidArgumentException('BooleanUserIntentFactory only supports "1" or "0"');
        }

        $value = boolval($value);

        return new SetBooleanValue(
            $target->getCode(),
            $target->getChannel(),
            $target->getLocale(),
            $value,
        );
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof AttributeTarget && 'pim_catalog_boolean' === $target->getType();
    }
}
