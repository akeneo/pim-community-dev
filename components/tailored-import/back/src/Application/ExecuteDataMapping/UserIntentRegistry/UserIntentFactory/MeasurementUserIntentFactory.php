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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class MeasurementUserIntentFactory implements UserIntentFactoryInterface
{
    /**
     * @param AttributeTarget $target
     * @param MeasurementValue $value
     */
    public function create(TargetInterface $target, ValueInterface $value): ValueUserIntent
    {
        return new SetMeasurementValue(
            $target->getCode(),
            $target->getChannel(),
            $target->getLocale(),
            $value->getValue(),
            $value->getUnit(),
        );
    }

    public function supports(TargetInterface $target, ValueInterface $value): bool
    {
        return $target instanceof AttributeTarget
            && 'pim_catalog_metric' === $target->getAttributeType()
            && $value instanceof MeasurementValue;
    }
}
