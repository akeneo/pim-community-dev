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

class ChannelShouldExistValidator extends ConstraintValidator
{
    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;

    public function __construct(ChannelExistsWithLocaleInterface $channelExistsWithLocale)
    {
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($channel, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ChannelShouldExist::class);
        if (!is_string($channel)) {
            return;
        }

        if (!$this->channelExistsWithLocale->doesChannelExist($channel)) {
            $this->context->buildViolation(
                ChannelShouldExist::NOT_EXIST_MESSAGE,
                [
                    '{{ channel_code }}' => $channel,
                ]
            )->addViolation();
        }
    }
}
