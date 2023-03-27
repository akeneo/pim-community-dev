<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type ReferenceEntityAttributeSource array{sub_source: string, sub_scope: string|null, sub_locale: string|null}
 * @phpstan-import-type ReferenceEntityAttribute from FindOneReferenceEntityAttributeByIdentifierQueryInterface
 */
final class ReferenceEntityAttributeSourceContainsValidLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneReferenceEntityAttributeByIdentifierQueryInterface $findOneReferenceEntityAttributeByIdentifierQuery,
        private GetLocalesQueryInterface $getLocalesQuery,
        private GetChannelLocalesQueryInterface $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReferenceEntityAttributeSourceContainsValidLocale) {
            throw new UnexpectedTypeException($constraint, ReferenceEntityAttributeSourceContainsValidLocale::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var ReferenceEntityAttributeSource $value */

        $referenceEntityAttribute = $this->findOneReferenceEntityAttributeByIdentifierQuery->execute($value['sub_source']);

        if (null === $referenceEntityAttribute) {
            throw new \LogicException('ReferenceEntity attribute not found');
        }

        $this->validateNonLocalizableSourceHasNoLocale($referenceEntityAttribute, $value);
        $this->validateLocalizableSourceHasLocale($referenceEntityAttribute, $value);
        $this->validateNonScopableAndLocalizableSourceHasAnyValidLocale($referenceEntityAttribute, $value);
        $this->validateScopableAndLocalizableSourceHasValidChannelLocale($referenceEntityAttribute, $value);
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateNonLocalizableSourceHasNoLocale(array $referenceEntityAttribute, array $value): void
    {
        if (!$referenceEntityAttribute['localizable'] && null !== $value['sub_locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.not_empty')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateLocalizableSourceHasLocale(array $referenceEntityAttribute, array $value): void
    {
        if ($referenceEntityAttribute['localizable'] && null === $value['sub_locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.empty')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateNonScopableAndLocalizableSourceHasAnyValidLocale(array $referenceEntityAttribute, array $value): void
    {
        if (!$referenceEntityAttribute['localizable'] || $referenceEntityAttribute['scopable']) {
            return;
        }

        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['sub_locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.unknown')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }

    /**
     * @param ReferenceEntityAttribute $referenceEntityAttribute
     * @param ReferenceEntityAttributeSource $value
     */
    private function validateScopableAndLocalizableSourceHasValidChannelLocale(array $referenceEntityAttribute, array $value): void
    {
        if (!$referenceEntityAttribute['localizable'] || !$referenceEntityAttribute['scopable']) {
            return;
        }

        if (null === $value['sub_scope']) {
            return;
        }

        try {
            $locales = $this->getChannelLocalesQuery->execute($value['sub_scope']);
        } catch (\LogicException) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.unknown')
                ->atPath('[sub_scope]')
                ->addViolation();

            return;
        }

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['sub_locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.disabled')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }
}
