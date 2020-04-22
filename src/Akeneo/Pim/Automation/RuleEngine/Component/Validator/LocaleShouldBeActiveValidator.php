<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class LocaleShouldBeActiveValidator extends ConstraintValidator
{
    /** @var ChannelExistsWithLocaleInterface */
    private $channelExistsWithLocale;

    public function __construct(ChannelExistsWithLocaleInterface $channelExistsWithLocale)
    {
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($localeCode, Constraint $constraint)
    {
        if (null === $localeCode) {
            return;
        }

        Assert::string($localeCode);
        Assert::isInstanceOf($constraint, LocaleShouldBeActive::class);

        if (!$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%locale_code%', $localeCode)
                ->addViolation();
        }
    }
}
