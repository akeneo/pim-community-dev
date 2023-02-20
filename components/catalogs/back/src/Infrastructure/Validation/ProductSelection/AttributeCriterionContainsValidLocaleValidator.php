<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

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
 * @phpstan-type AttributeCriterion array{field: string, scope: string|null, locale: string|null}
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
final class AttributeCriterionContainsValidLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetLocalesQueryInterface $getLocalesQuery,
        private GetChannelLocalesQueryInterface $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeCriterionContainsValidLocale) {
            throw new UnexpectedTypeException($constraint, AttributeCriterionContainsValidLocale::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeCriterion $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['field']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateNonLocalizableCriterionHasNoLocale($attribute, $value);
        $this->validateLocalizableCriterionHasLocale($attribute, $value);
        $this->validateNonScopableAndLocalizableCriterionHasAnyValidLocale($attribute, $value);
        $this->validateScopableAndLocalizableCriterionHasValidChannelLocale($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateNonLocalizableCriterionHasNoLocale(array $attribute, array $value): void
    {
        if (!$attribute['localizable'] && null !== $value['locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.not_empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateLocalizableCriterionHasLocale(array $attribute, array $value): void
    {
        if ($attribute['localizable'] && null === $value['locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateNonScopableAndLocalizableCriterionHasAnyValidLocale(array $attribute, array $value): void
    {
        if (!$attribute['localizable'] || $attribute['scopable']) {
            return;
        }

        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.unknown')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param Attribute $attribute
     * @param AttributeCriterion $value
     */
    private function validateScopableAndLocalizableCriterionHasValidChannelLocale(array $attribute, array $value): void
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
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
