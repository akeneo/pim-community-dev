<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeFilterValidatorHelper
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $scopeRepository;

    /** @var array */
    protected $localeCodes;

    /** @var array */
    protected $scopeCodes;

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
    }

    /**
     * Check if scope data is consistent with the attribute scopable property
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

    private function getActivatedLocaleCodes(): array
    {
        return $this->localeRepository->getActivatedLocaleCodes();
    }

    private function getScopeCodes(): array
    {
        return $this->scopeRepository->getChannelCodes();
    }
}
