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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Price;

use Symfony\Component\Validator\Constraint;

final class PriceSourceConfiguration extends Constraint
{
    public function __construct(
        private ?string $channelCode,
    ) {
        parent::__construct();
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }
}
