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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class MultiSelectUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param AttributeTarget $target
     */
    public function create(TargetInterface $target, ValueInterface $value): ValueUserIntent
    {
        if (!$this->supports($target)) {
            throw new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_multiselect"');
        }

        if (!$value instanceof ArrayValue
            && !$value instanceof StringValue
        ) {
            throw new UnexpectedValueException($value, [ArrayValue::class, StringValue::class], self::class);
        }

        if ($value instanceof StringValue) {
            $value = new ArrayValue([$value->getValue()]);
        }

        return match ($target->getActionIfNotEmpty()) {
            TargetInterface::ACTION_ADD => new AddMultiSelectValue(
                $target->getCode(),
                $target->getChannel(),
                $target->getLocale(),
                $value->getValue(),
            ),
            TargetInterface::ACTION_SET => new SetMultiSelectValue(
                $target->getCode(),
                $target->getChannel(),
                $target->getLocale(),
                $value->getValue(),
            ),
            default => throw new \LogicException('Unknown action if not empty'),
        };
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof AttributeTarget && 'pim_catalog_multiselect' === $target->getAttributeType();
    }
}
