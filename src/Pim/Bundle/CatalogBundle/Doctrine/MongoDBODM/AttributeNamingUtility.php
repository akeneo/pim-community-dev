<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Provides util methods to get attributes codes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNamingUtility
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
     * @param  array $codes
     * @param  array $suffixes
     * @param  array $separator
     *
     * @return array
     */
    public function appendSuffixes($codes, $suffixes, $separator = '-') {
        $result = $codes;

        if (count($suffixes) > 0) {
            $result = [];

            foreach ($codes as $key => $code) {
                foreach ($suffixes as $suffix) {
                    $result[] = $code . $separator . $suffix;
                }
            }
        }

        return $result;
    }

    /**
     * Get all locale prefixes
     *
     * @return array
     */
    public function getLocaleCodes(AbstractAttribute $attribute = null)
    {
        $localeSuffixes = [];

        if (null === $attribute || $attribute->isLocalizable()) {
            foreach ($this->getActivatedLocales() as $locale) {
                $localeSuffixes[] = $locale->getCode();
            }
        }

        return $localeSuffixes;
    }

    /**
     * Get all channel prefixes
     *
     * @return array
     */
    public function getChannelCodes(AbstractAttribute $attribute = null)
    {
        $channelSuffixes = [];

        if (null === $attribute || $attribute->isScopable()) {
            foreach ($this->getChannels() as $channel) {
                $channelSuffixes[] = $channel->getCode();
            }
        }

        return $channelSuffixes;
    }

    /**
     * Get all currency
     *
     * @return array
     */
    public function getCurrencyCodes()
    {
        $currencySuffixes = [];

        foreach ($this->getCurrencies() as $currency) {
            $currencySuffixes[] = $currency->getCode();
        }

        return $currencySuffixes;
    }

    /**
     * Get the attribute fields name for normalizedData
     *
     * @param AbstractAttribute $attribute
     *
     * @return string[]
     */
    public function getAttributeNormFields(AbstractAttribute $attribute)
    {
        $localeCodes  = $this->getLocaleCodes($attribute);
        $channelCodes = $this->getChannelCodes($attribute);

        $normFields = [ProductQueryUtility::NORMALIZED_FIELD . '.' . $attribute->getCode()];
        $normFields = $this->attributeNamingUtility->appendSuffixes($normFields, $localeCodes, '-');
        $normFields = $this->attributeNamingUtility->appendSuffixes($normFields, $channelCodes, '-');

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
     *
     * @return AbstractAttribute[]
     */
    public function getPricesAttributes($onlyInGrid = true)
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['backendType' => 'prices', 'useableAsGridFilter' => true]);

        return $attributes;
    }

    /**
     * Get filterable scopable attributes
     *
     * @return AbstractAttribute[]
     */
    public function getScopableAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['scopable' => true, 'useableAsGridFilter' => true]);

        return $attributes;
    }

    /**
     * Get filterable localizable attributes
     *
     * @return AbstractAttribute[]
     */
    public function getLocalizableAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['localizable' => true, 'useableAsGridFilter' => true]);

        return $attributes;
    }
}
