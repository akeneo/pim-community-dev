<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LabelCollectionValidator extends ConstraintValidator
{
    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;

    public function __construct(ChannelExistsWithLocaleInterface $channelExistsWithLocale)
    {
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    public function validate($labels, Constraint $constraint)
    {
        if (!$constraint instanceof LabelCollection) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if (!\is_array($labels)) {
            return;
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        foreach (\array_keys($labels) as $localeCode) {
            $validator->validate($localeCode, [
                new NotBlank(),
                new Type(['type' => 'string']),
            ]);
            $this->validateActivatedLocale($localeCode);
        }
    }

    private function validateActivatedLocale($localeCode): void
    {
        if (!is_string($localeCode) || empty($localeCode)) {
            return;
        }

        if (!$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->addViolation(
                'pim_table_configuration.validation.table_configuration.label_locale_does_not_exist_or_is_not_activated',
                ['{{ locale_code }}' => $localeCode]
            );
        }
    }
}
