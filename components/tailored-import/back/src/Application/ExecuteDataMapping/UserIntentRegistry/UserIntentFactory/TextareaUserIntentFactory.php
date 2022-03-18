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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetInterface;

class TextareaUserIntentFactory implements UserIntentFactoryInterface
{
    public function create(TargetInterface $target, string $value): ValueUserIntent
    {
        if (!$target instanceof TargetAttribute) {
            throw new \InvalidArgumentException('The target must be a TargetAttribute');
        }

        return new SetTextareaValue(
            $target->getCode(),
            $target->getChannel(),
            $target->getLocale(),
            $value,
        );
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof TargetAttribute && 'pim_catalog_textarea' === $target->getType();
    }
}
