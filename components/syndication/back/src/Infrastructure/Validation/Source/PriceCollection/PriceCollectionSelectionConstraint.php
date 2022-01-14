<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\PriceCollection;

use Symfony\Component\Validator\Constraint;

class PriceCollectionSelectionConstraint extends Constraint
{
    public const CURRENCY_SHOULD_BE_ACTIVATE_ON_CHANNEL_MESSAGE = 'akeneo.syndication.validation.currency.should_be_active_on_channel';
    public const CURRENCY_SHOULD_BE_ACTIVATE_MESSAGE = 'akeneo.syndication.validation.currency.should_be_active';

    public ?string $channelReference;

    public function validatedBy(): string
    {
        return PriceCollectionSelectionValidator::class;
    }

    public function getRequiredOptions(): array
    {
        return ['channelReference'];
    }
}
