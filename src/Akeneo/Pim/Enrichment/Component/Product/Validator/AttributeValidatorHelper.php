<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableLocaleException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableSpecificLocaleException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * AttributeValidatorHelper
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidatorHelper
{
    protected LocaleRepositoryInterface $localeRepository;

    protected ChannelRepositoryInterface $scopeRepository;

    protected ?array $localeCodes = null;

    protected ?array $scopeCodes = null;

    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $scopeRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->scopeRepository = $scopeRepository;
    }

    /**
     * Check if locale data is consistent with the attribute localizable property
     *
     * @throws \LogicException
     */
    public function validateLocale(AttributeInterface $attribute, ?string $locale): void
    {
        if (!$attribute->isLocalizable() && null === $locale) {
            return;
        }

        if ($attribute->isLocalizable() && null === $locale) {
            throw LocalizableAttributeException::withCode($attribute->getCode());
        }

        if (!$attribute->isLocalizable() && null !== $locale) {
            throw NotLocalizableAttributeException::withCode($attribute->getCode());
        }

        if (null === $this->localeCodes) {
            $this->localeCodes = $this->getActivatedLocaleCodes();
        }

        if (!in_array($locale, $this->localeCodes)) {
            throw new UnavailableLocaleException(
                sprintf(
                    'Attribute "%s" expects an existing and activated locale, "%s" given.',
                    $attribute->getCode(),
                    $locale
                )
            );
        }

        if ($attribute->isLocaleSpecific() && !in_array($locale, $attribute->getAvailableLocaleCodes())) {
            throw new UnavailableSpecificLocaleException(
                sprintf(
                    'Attribute "%s" is locale specific and expects one of these locales: %s, "%s" given.',
                    $attribute->getCode(),
                    implode($attribute->getAvailableLocaleCodes(), ', '),
                    $locale
                )
            );
        }
    }

    /**
     * Check if metric family of attribute are the same
     */
    public function validateUnitFamilies(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ): void {
        if ($fromAttribute->getMetricFamily() !== $toAttribute->getMetricFamily()) {
            throw new \LogicException(
                sprintf(
                    'Metric families are not the same for attributes: "%s" and "%s".',
                    $fromAttribute->getCode(),
                    $toAttribute->getCode()
                )
            );
        }
    }

    /**
     * Check if scope data is consistent with the attribute scopable property
     *
     * @throws \LogicException
     */
    public function validateScope(AttributeInterface $attribute, ?string $scope): void
    {
        if (!$attribute->isScopable() && null === $scope) {
            return;
        }

        if ($attribute->isScopable() && null === $scope) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" expects a scope, none given.',
                    $attribute->getCode()
                )
            );
        }

        if (!$attribute->isScopable() && null !== $scope) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" does not expect a scope, "%s" given.',
                    $attribute->getCode(),
                    $scope
                )
            );
        }

        if (null === $this->scopeCodes) {
            $this->scopeCodes = $this->getScopeCodes();
        }

        if (!in_array($scope, $this->scopeCodes)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" expects an existing scope, "%s" given.',
                    $attribute->getCode(),
                    $scope
                )
            );
        }
    }

    protected function getActivatedLocaleCodes(): array
    {
        return $this->localeRepository->getActivatedLocaleCodes();
    }

    protected function getScopeCodes(): array
    {
        return $this->scopeRepository->getChannelCodes();
    }
}
