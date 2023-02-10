<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeSource array{source: string, scope: string|null, locale: string|null}
 * @phpstan-type Attribute array{
 *    attribute_group_code: string,
 *    attribute_group_label: string,
 *    code: string,
 *    default_measurement_unit?: string,
 *    label: string,
 *    localizable: bool,
 *    measurement_family?: string,
 *    scopable: bool,
 *    type: string
 * }
 */
final class AttributeSourceContainsValidLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetLocalesQueryInterface $getLocalesQuery,
        private GetChannelLocalesQueryInterface $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeSourceContainsValidLocale) {
            throw new UnexpectedTypeException($constraint, AttributeSourceContainsValidLocale::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeSource $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonLocalizableSourceHasNoLocale($attribute, $value);
        $this->validateLocalizableSourceHasLocale($attribute, $value);
        $this->validateNonScopableAndLocalizableSourceHasAnyValidLocale($attribute, $value);
        $this->validateScopableAndLocalizableSourceHasValidChannelLocale($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateNonLocalizableSourceHasNoLocale(array $attribute, array $value): void
    {
        if (!$attribute['localizable'] && null !== $value['locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.not_empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateLocalizableSourceHasLocale(array $attribute, array $value): void
    {
        if ($attribute['localizable'] && null === $value['locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateNonScopableAndLocalizableSourceHasAnyValidLocale(array $attribute, array $value): void
    {
        if (!$attribute['localizable'] || $attribute['scopable']) {
            return;
        }

        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.unknown')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeSource $value
     */
    private function validateScopableAndLocalizableSourceHasValidChannelLocale(array $attribute, array $value): void
    {
        if (!$attribute['localizable'] || !$attribute['scopable']) {
            return;
        }

        if (null === $value['scope']) {
            return;
        }

        try {
            $locales = $this->getChannelLocalesQuery->execute($value['scope']);
        } catch (\LogicException) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
