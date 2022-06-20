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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class EnabledUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param PropertyTarget $target
     */
    public function create(TargetInterface $target, ValueInterface $value): SetEnabled
    {
        if (!$this->supports($target)) {
            throw new \InvalidArgumentException('The target must be a PropertyTarget and be of type "enabled"');
        }

        // TODO replace with BooleanValue when having EnabledReplacementOperation
        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        if (!\in_array($value->getValue(), ['1', '0'], true)) {
            throw new \InvalidArgumentException('Enabled property can only be set with "1" or "0"');
        }

        return new SetEnabled((bool) $value->getValue());
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof PropertyTarget && 'enabled' === $target->getCode();
    }
}
