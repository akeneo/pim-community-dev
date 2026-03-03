<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndScopableAttributeException;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * This class validate the minimal requirement to create a value according to an attribute
 * about the channel and the scope.
 *
 * It does not validate the existence or the consistency of the locale according to the channel, as this check
 * should be done in validation layer.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateAttribute
{
    public static function validate(Attribute $attribute, ?string $channelCode, ?string $localeCode): void
    {
        if ($attribute->isLocalizableAndScopable() && (null === $channelCode || null === $localeCode)) {
            throw LocalizableAndScopableAttributeException::fromAttributeChannelAndLocale($attribute->code(), $channelCode, $localeCode);
        } elseif ((!$attribute->isScopable() && $attribute->isLocalizable()) && (null !== $channelCode || null === $localeCode)) {
            throw LocalizableAndNotScopableAttributeException::fromAttributeChannelAndLocale($attribute->code(), $channelCode, $localeCode);
        } elseif (($attribute->isScopable() && !$attribute->isLocalizable()) && (null === $channelCode || null !== $localeCode)) {
            throw NotLocalizableAndScopableAttributeException::fromAttributeChannelAndLocale($attribute->code(), $channelCode, $localeCode);
        } elseif ((!$attribute->isScopable() && !$attribute->isLocalizable()) && (null !== $channelCode || null !== $localeCode)) {
            throw NotLocalizableAndNotScopableAttributeException::fromAttributeChannelAndLocale($attribute->code(), $channelCode, $localeCode);
        }
    }
}
