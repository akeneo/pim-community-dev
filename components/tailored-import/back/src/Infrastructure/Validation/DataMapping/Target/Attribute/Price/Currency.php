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

final class Currency extends Constraint
{
    public const CURRENCY_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.source_configuration.currency_should_exist';

    public function __construct(
        private ?string $channelCode,
    ) {
        parent::__construct();
    }
}
