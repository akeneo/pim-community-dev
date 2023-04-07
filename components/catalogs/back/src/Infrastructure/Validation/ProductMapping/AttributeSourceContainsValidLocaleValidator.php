<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
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
 * @phpstan-import-type SourceAssociation from Catalog
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
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

        /** @var SourceAssociation $value */

        if (null === $value['source']) {
            return;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonLocalizableSourceHasNoLocale($attribute, $value['locale']);
        $this->validateLocalizableSourceHasLocale($attribute, $value['locale']);
        $this->validateNonScopableAndLocalizableSourceHasAnyValidLocale($attribute, $value['locale']);
        $this->validateScopableAndLocalizableSourceHasValidChannelLocale($attribute, $value['scope'], $value['locale']);
    }

    /**
     * @param Attribute $attribute
     */
    private function validateNonLocalizableSourceHasNoLocale(array $attribute, ?string $localeCode): void
    {
        if (!$attribute['localizable'] && null !== $localeCode) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.not_empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     */
    private function validateLocalizableSourceHasLocale(array $attribute, ?string $localeCode): void
    {
        if ($attribute['localizable'] && null === $localeCode) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     */
    private function validateNonScopableAndLocalizableSourceHasAnyValidLocale(array $attribute, ?string $localeCode): void
    {
        if (!$attribute['localizable'] || $attribute['scopable']) {
            return;
        }

        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $localeCode)) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.unknown')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     */
    private function validateScopableAndLocalizableSourceHasValidChannelLocale(array $attribute, ?string $scope, ?string $localeCode): void
    {
        if (!$attribute['localizable'] || !$attribute['scopable']) {
            return;
        }

        if (null === $scope) {
            return;
        }

        try {
            $locales = $this->getChannelLocalesQuery->execute($scope);
        } catch (\LogicException) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $localeCode)) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
