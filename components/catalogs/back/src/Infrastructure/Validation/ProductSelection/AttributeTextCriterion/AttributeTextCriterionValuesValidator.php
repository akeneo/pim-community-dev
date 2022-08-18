<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion;

use Akeneo\Catalogs\Application\Persistence\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\GetChannelLocalesQuery;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor *
 * @phpstan-type AttributeTextCriterion array{field: string, operator: string, value: string, scope: string, locale: string}
 */
class AttributeTextCriterionValuesValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private GetChannelQueryInterface $getChannelQuery,
        private GetChannelLocalesQuery $getChannelLocalesQuery,
        private GetLocalesQueryInterface $getLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var AttributeTextCriterion $value */

        if (!$constraint instanceof AttributeTextCriterionValues) {
            throw new UnexpectedTypeException($constraint, AttributeTextCriterionValues::class);
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['field']);
        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        if ($attribute['localizable'] && $attribute['scopable']) {
            $this->validateScopeAndLocale($value);
        }

        if (!$attribute['localizable'] && $attribute['scopable']) {
            $this->validateScope($value);
        }

        if ($attribute['localizable'] && !$attribute['scopable']) {
            $this->validateLocale($value);
        }

        if (\in_array($value['operator'], ['EMPTY', 'NOT_EMPTY']) && '' !== $value['value']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.attribute_text.value.not_empty')
                ->atPath('[locale]')
                ->addViolation();
        }

        if (!\in_array($value['operator'], ['EMPTY', 'NOT_EMPTY']) && '' === $value['value']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.attribute_text.value.empty')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param AttributeTextCriterion $value
     */
    private function validateScopeAndLocale(array $value): void
    {
        try {
            $activeLocales = $this->getChannelLocalesQuery->execute($value['scope']);
        } catch (\LogicException) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $localeIsValid = 0 < \count(
            \array_filter(
                $activeLocales,
                static fn (array $locale) => $locale['code'] === $value['locale']
            )
        );

        if (!$localeIsValid) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param AttributeTextCriterion $value
     */
    private function validateScope(array $value): void
    {
        $channel = $this->getChannelQuery->execute($value['scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    /**
     * @param AttributeTextCriterion $value
     */
    private function validateLocale(array $value): void
    {
        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale) => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.unknown')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
