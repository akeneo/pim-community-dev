<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
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
            $message = 'Attribute "%s" expects a channel code and a locale code, "%s" channel code and "%s" locale code given.';

            throw new InvalidAttributeException(
                'attribute',
                null,
                ValueFactory::class,
                sprintf($message, $attribute->code(), $channelCode, $localeCode),
            );
        } elseif ((!$attribute->isScopable() && $attribute->isLocalizable()) && (null !== $channelCode || null === $localeCode)) {
            $message = 'Attribute "%s" expects a locale code and a null channel code, "%s" channel code and "%s" locale code given.';

            throw new InvalidAttributeException(
                'attribute',
                null,
                ValueFactory::class,
                sprintf($message, $attribute->code(), $channelCode, $localeCode),
            );
        } elseif (($attribute->isScopable() && !$attribute->isLocalizable()) && (null === $channelCode || null !== $localeCode)) {
            $message = 'Attribute "%s" expects a channel code and a null locale code, "%s" channel code and "%s" locale code given.';

            throw new InvalidAttributeException(
                'attribute',
                null,
                ValueFactory::class,
                sprintf($message, $attribute->code(), $channelCode, $localeCode),
            );
        } elseif ((!$attribute->isScopable() &&! $attribute->isLocalizable()) && (null !== $channelCode || null !== $localeCode)) {
            $message = 'Attribute "%s" expects a null channel code and a null locale code, "%s" channel code and "%s" locale code given.';

            throw new InvalidAttributeException(
                'attribute',
                null,
                ValueFactory::class,
                sprintf($message, $attribute->code(), $channelCode, $localeCode),
            );
        }
    }
}
