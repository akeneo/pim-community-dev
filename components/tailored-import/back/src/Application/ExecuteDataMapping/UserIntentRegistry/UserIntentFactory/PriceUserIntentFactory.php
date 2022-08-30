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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue as PriceValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class PriceUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param AttributeTarget $target
     * @param PriceValue $value
     */
    public function create(TargetInterface $target, ValueInterface $value): ValueUserIntent
    {
        return new SetPriceValue(
            $target->getCode(),
            $target->getChannel(),
            $target->getLocale(),
            new PriceValueUserIntent($value->getValue(), $value->getCurrency()),
        );
    }

    public function supports(TargetInterface $target, ValueInterface $value): bool
    {
        return $target instanceof AttributeTarget
            && 'pim_catalog_price_collection' === $target->getAttributeType()
            && $value instanceof PriceValue;
    }
}
