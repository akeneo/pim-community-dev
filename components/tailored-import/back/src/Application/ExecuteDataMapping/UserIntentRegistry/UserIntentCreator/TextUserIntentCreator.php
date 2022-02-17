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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentCreator;

use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\UserIntent;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentCreatorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetInterface;

class TextUserIntentCreator implements UserIntentCreatorInterface
{
    public function create(TargetInterface $target, string $value): UserIntent
    {
        if (!$target instanceof TargetAttribute) {
            throw new \InvalidArgumentException('The target must be a TargetAttribute');
        }

        return new SetTextValue(
            $target->getCode(),
            $target->getLocale(),
            $target->getChannel(),
            $value
        );
    }

    public function supports(TargetInterface $target): bool
    {
        return $target instanceof TargetAttribute && $target->getType() === 'pim_catalog_text';
    }
}
