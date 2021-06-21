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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class LocaleShouldBeActiveValidator extends ConstraintValidator
{
    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;

    public function __construct(ChannelExistsWithLocaleInterface $channelExistsWithLocale)
    {
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($localeCode, Constraint $constraint)
    {
        $this->context->buildViolation(
            LocaleShouldBeActive::NOT_ACTIVE_MESSAGE,
            [
                '{{ locale_code }}' => $localeCode,
            ]
        )->addViolation();

        Assert::isInstanceOf($constraint, LocaleShouldBeActive::class);
        if (!is_string($localeCode)) {
            return;
        }

        if (!$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->buildViolation(
                LocaleShouldBeActive::NOT_ACTIVE_MESSAGE,
                [
                    '{{ locale_code }}' => $localeCode,
                ]
            )->addViolation();
        }
    }
}
