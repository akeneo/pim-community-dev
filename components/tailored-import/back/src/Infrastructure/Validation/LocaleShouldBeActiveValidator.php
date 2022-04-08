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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class LocaleShouldBeActiveValidator extends ConstraintValidator
{
    public function __construct(
        private ChannelExistsWithLocaleInterface $channelExistsWithLocale,
    ) {
    }

    public function validate($localeCode, Constraint $constraint): void
    {
        if (!$constraint instanceof LocaleShouldBeActive) {
            throw new UnexpectedTypeException($constraint, LocaleShouldBeActive::class);
        }

        if (!is_string($localeCode)) {
            return;
        }

        if (!$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->buildViolation(
                LocaleShouldBeActive::NOT_ACTIVE,
                [
                    '{{ locale_code }}' => $localeCode,
                ]
            )->addViolation();
        }
    }
}
