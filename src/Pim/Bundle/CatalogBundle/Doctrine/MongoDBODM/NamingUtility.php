<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Provides util methods to get attributes codes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NamingUtility
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $channelClass;

    /** @var string */
    protected $localeClass;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $currencyClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $channelClass
     * @param string          $localeClass
     * @param string          $attributeClass
     * @param string          $currencyClass
     */
    public function __construct($managerRegistry, $channelClass, $localeClass, $attributeClass, $currencyClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->channelClass    = $channelClass;
        $this->localeClass     = $localeClass;
        $this->attributeClass  = $attributeClass;
        $this->currencyClass   = $currencyClass;
    }

    /**
     * Append given suffixes to codes
     * @param string[] $codes
     * @param string[] $suffixes
     * @param string   $separator
     *
     * @return array
     */
    public function appendSuffixes($codes, $suffixes, $separator = '-')
    {
        $result = $codes;

        if (count($suffixes) > 0) {
            $result = [];

            foreach ($codes as $code) {
                foreach ($suffixes as $suffix) {
                    $result[] = $code . $separator . $suffix;
                }
            }
        }

        return $result;
    }

    /**
     * Get all locale codes
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    public function getLocaleCodes(AbstractAttribute $attribute = null)
    {
        $localeCodes = [];

        if (null === $attribute || $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                $localeCodes[] = $locale->getCode();
            }
        }

        return $localeCodes;
    }

    /**
     * Get all channel codes
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    public function getChannelCodes(AbstractAttribute $attribute = null)
    {
        $channelCodes = [];

        if (null === $attribute || $attribute->isScopable()) {
            foreach ($this->getChannels() as $channel) {
                $channelCodes[] = $channel->getCode();
            }
        }

        return $channelCodes;
    }

    /**
     * Get all currency codes
     *
     * @return array
     */
    public function getCurrencyCodes()
    {
        $currencyCodes = [];

        foreach ($this->getCurrencies() as $currency) {
            $currencyCodes[] = $currency->getCode();
        }

        return $currencyCodes;
    }

    /**
     * Get the attribute fields codes for normalizedData
     *
     * @param AbstractAttribute $attribute
     * @param string            $prefix
     *
     * @return string[]
     */
    public function getAttributeNormFields(AbstractAttribute $attribute, $prefix = null)
    {
        $localeCodes  = $this->getLocaleCodes($attribute);
        $channelCodes = $this->getChannelCodes($attribute);

        $normFields = [
            (
                null === $prefix ?
                ProductQueryUtility::NORMALIZED_FIELD . ProductQueryUtility::ELEMENT_TOKEN_SEPARATOR :
                $prefix
            ) . $attribute->getCode()
        ];

        $normFields = $this->appendSuffixes($normFields, $localeCodes);
        $normFields = $this->appendSuffixes($normFields, $channelCodes);

        return $normFields;
    }

    /**
     * Get all channels
     *
     * @return Channel[]
     */
    public function getChannels()
    {
        $channelManager    = $this->managerRegistry->getManagerForClass($this->channelClass);
        $channelRepository = $channelManager->getRepository($this->channelClass);

        return $channelRepository->findAll();
    }

    /**
     * Get active currencies
     *
     * @return Currency[]
     */
    public function getCurrencies()
    {
        $currencyManager = $this->managerRegistry->getManagerForClass($this->currencyClass);
        $currencyRepository = $currencyManager->getRepository($this->currencyClass);

        return $currencyRepository->getActivatedCurrencies();
    }

    /**
     * Get active locales
     *
     * @return Locale[]
     */
    public function getLocales()
    {
        $localeManager    = $this->managerRegistry->getManagerForClass($this->localeClass);
        $localeRepository = $localeManager->getRepository($this->localeClass);

        return $localeRepository->getActivatedLocales();
    }

    /**
     * Get filterable prices backend type attribute
     * @param bool $onlyInGrid
     *
     * @return AbstractAttribute[]
     */
    public function getPricesAttributes($onlyInGrid = true)
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $criteria = ['backendType' => 'prices'];

        if ($onlyInGrid) {
            $criteria['useableAsGridFilter'] = true;
        }

        $attributes = $attributeRepository->findBy($criteria);

        return $attributes;
    }

    /**
     * Get filterable scopable attributes
     * @param bool $onlyInGrid
     *
     * @return AbstractAttribute[]
     */
    public function getScopableAttributes($onlyInGrid = true)
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $criteria = ['scopable' => true];

        if ($onlyInGrid) {
            $criteria['useableAsGridFilter'] = true;
        }

        $attributes = $attributeRepository->findBy($criteria);

        return $attributes;
    }

    /**
     * Get filterable localizable attributes
     * @param bool $onlyInGrid
     *
     * @return AbstractAttribute[]
     */
    public function getLocalizableAttributes($onlyInGrid = true)
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $criteria = ['localizable' => true];

        if ($onlyInGrid) {
            $criteria['useableAsGridFilter'] = true;
        }

        $attributes = $attributeRepository->findBy($criteria);

        return $attributes;
    }
}
