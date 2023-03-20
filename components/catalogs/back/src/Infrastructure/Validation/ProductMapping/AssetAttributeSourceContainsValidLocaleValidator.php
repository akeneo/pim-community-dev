<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
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
 * @phpstan-type AssetAttributeSource array{sub_source: string, sub_scope: string|null, sub_locale: string|null}
 * @phpstan-import-type AssetAttribute from FindOneAssetAttributeByIdentifierQueryInterface
 */
final class AssetAttributeSourceContainsValidLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAssetAttributeByIdentifierQueryInterface $findOneAssetAttributeByIdentifierQuery,
        private GetLocalesQueryInterface $getLocalesQuery,
        private GetChannelLocalesQueryInterface $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AssetAttributeSourceContainsValidLocale) {
            throw new UnexpectedTypeException($constraint, AssetAttributeSourceContainsValidLocale::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AssetAttributeSource $value */

        $assetAttribute = $this->findOneAssetAttributeByIdentifierQuery->execute($value['sub_source']);

        if (null === $assetAttribute) {
            throw new \LogicException('Asset attribute not found');
        }

        $this->validateNonLocalizableSourceHasNoLocale($assetAttribute, $value);
        $this->validateLocalizableSourceHasLocale($assetAttribute, $value);
        $this->validateNonScopableAndLocalizableSourceHasAnyValidLocale($assetAttribute, $value);
        $this->validateScopableAndLocalizableSourceHasValidChannelLocale($assetAttribute, $value);
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateNonLocalizableSourceHasNoLocale(array $assetAttribute, array $value): void
    {
        if (!$assetAttribute['localizable'] && null !== $value['sub_locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.not_empty')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateLocalizableSourceHasLocale(array $assetAttribute, array $value): void
    {
        if ($assetAttribute['localizable'] && null === $value['sub_locale']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.locale.empty')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateNonScopableAndLocalizableSourceHasAnyValidLocale(array $assetAttribute, array $value): void
    {
        if (!$assetAttribute['localizable'] || $assetAttribute['scopable']) {
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
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateScopableAndLocalizableSourceHasValidChannelLocale(array $assetAttribute, array $value): void
    {
        if (!$assetAttribute['localizable'] || !$assetAttribute['scopable']) {
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
