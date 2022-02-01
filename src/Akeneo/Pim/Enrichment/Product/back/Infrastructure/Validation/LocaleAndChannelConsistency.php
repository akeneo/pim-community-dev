<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocaleAndChannelConsistency extends Constraint
{
    public const NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE = 'test1';
    public const CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE = 'test2';
    public const NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE = 'test3';
    public const LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE = 'test4';
    public const INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE = 'test5';
    public const CHANNEL_DOES_NOT_EXIST = 'test7';
    public const LOCALE_IS_NOT_ACTIVE = 'test8';
    public const LOCALE_NOT_BOUND_TO_CHANNEL = 'test9';

    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
