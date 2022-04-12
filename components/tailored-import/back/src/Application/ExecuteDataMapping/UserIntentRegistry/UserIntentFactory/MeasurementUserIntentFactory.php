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
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;

final class MeasurementUserIntentFactory implements UserIntentFactoryInterface
{
    public function create(TargetInterface $target, string|array $value): ValueUserIntent
    {
        if (!$this->supports($target)) {
            throw new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_metric"');
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('The value must be a string "%s" given', gettype($value)));
        }

        $sourceConfiguration = $target->getSourceConfiguration();
        if (!$sourceConfiguration instanceof MeasurementSourceConfiguration) {
            throw new \InvalidArgumentException('The target source configuration must be a MeasurementSourceConfiguration');
        }

        return new SetMeasurementValue(
            $target->getCode(),
            $target->getChannel(),
            $target->getLocale(),
            $value,
            $sourceConfiguration->getUnit(),
        );
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof AttributeTarget && 'pim_catalog_metric' === $target->getType();
    }
}
