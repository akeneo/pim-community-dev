<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocaleAndChannelShouldBeConsistent extends Constraint
{
    public const NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE = 'pim_enrich.product.validation.upsert.set_value.no_channel_code_provided_for_scopable_attribute';
    public const CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE = 'pim_enrich.product.validation.upsert.set_value.channel_code_provided_for_non_scopable_attribute';
    public const NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE = 'pim_enrich.product.validation.upsert.set_value.no_locale_code_provided_for_localizable_attribute';
    public const LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE = 'pim_enrich.product.validation.upsert.set_value.locale_code_provided_for_non_localizable_attribute';
    public const INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE = 'pim_enrich.product.validation.upsert.set_value.invalid_locale_code_for_locale_specific_attribute';
    public const CHANNEL_DOES_NOT_EXIST = 'pim_enrich.product.validation.upsert.set_value.channel_does_not_exist';
    public const LOCALE_IS_NOT_ACTIVE = 'pim_enrich.product.validation.upsert.set_value.locale_is_not_active';
    public const LOCALE_NOT_ACTIVATED_FOR_CHANNEL = 'pim_enrich.product.validation.upsert.set_value.locale_not_activated_for_channel';
}
