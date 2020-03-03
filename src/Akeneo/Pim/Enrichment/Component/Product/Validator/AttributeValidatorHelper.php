<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
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
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $scopeRepository;

    /** @var array */
    protected $localeCodes;

    /** @var array */
    protected $scopeCodes;

    /**
     * @param LocaleRepositoryInterface  $localeRepository
     * @param ChannelRepositoryInterface $scopeRepository
     */
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
     * @param AttributeInterface $attribute
     * @param string             $locale
     *
     * @throws \LogicException
     */
    public function validateLocale(AttributeInterface $attribute, $locale)
    {
        if (!$attribute->isLocalizable() && null === $locale) {
            return;
        }

        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" expects a locale, none given.',
                    $attribute->getCode()
                )
            );
        }

        if (!$attribute->isLocalizable() && null !== $locale) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" does not expect a locale, "%s" given.',
                    $attribute->getCode(),
                    $locale
                )
            );
        }

        if (null === $this->localeCodes) {
            $this->localeCodes = $this->getActivatedLocaleCodes();
        }

        if (!in_array($locale, $this->localeCodes)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" expects an existing and activated locale, "%s" given.',
                    $attribute->getCode(),
                    $locale
                )
            );
        }

        if ($attribute->isLocaleSpecific() && !in_array($locale, $attribute->getAvailableLocaleCodes())) {
            throw new \LogicException(
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
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     */
    public function validateUnitFamilies(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ) {
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
     * @param AttributeInterface $attribute
     * @param string             $scope
     *
     * @throws \LogicException
     */
    public function validateScope(AttributeInterface $attribute, $scope)
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

    /**
     * @return array
     */
    protected function getActivatedLocaleCodes()
    {
        return $this->localeRepository->getActivatedLocaleCodes();
    }

    /**
     * @return array
     */
    protected function getScopeCodes()
    {
        return $this->scopeRepository->getChannelCodes();
    }
}
