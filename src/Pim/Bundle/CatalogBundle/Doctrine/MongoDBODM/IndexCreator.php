<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Create index for different entity requirements
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexCreator
{
    /** @var Collection */
    protected $collection;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $channelClass;

    /** @var string */
    protected $localeClass;

    /** @var string */
    protected $currencyClass;

    /** @var string */
    protected $attributeClass;

    /** @var array */
    protected $currencies;

    /** @var array */
    protected $channels;

    /** @var array */
    protected $locales;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     * @param string          $channelClass
     * @param string          $localeClass
     * @param string          $currencyClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $productClass,
        $channelClass,
        $localeClass,
        $currencyClass,
        $attributeClass
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->productClass    = $productClass;
        $this->channelClass    = $channelClass;
        $this->localeClass     = $localeClass;
        $this->currencyClass   = $currencyClass;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * Ensure indexes from attribute.
     * Indexes will be created on the normalizedData part for attribute
     * that are usable as column and as filter and for identifier and unique attribute
     *
     * @param AbstractAttribute $attribute
     */
    public function ensureIndexesFromAttribute(AbstractAttribute $attribute)
    {
        $attributeFields = $this->getAttributeNormFields($attribute);

        switch ($attribute->getBackendType()) {
            case "prices":
                $attributeFields = $this->addFieldsFromPrices($attributeFields, $attribute);
                break;
            case "option":
            case "options":
                $attributeFields = $this->addFieldsFromOption($attributeFields, $attribute);
                break;
        }
        $this->ensureIndexes($attributeFields);
    }

    /**
     * Ensure indexes from channel.
     *
     * Indexes will be created on the normalizedData part for:
     * - completenesses
     * - scopable attributes
     *
     * @param Channel $channel
     */
    public function ensureIndexesFromChannel(Channel $channel)
    {
        $completenessFields = $this->getCompletenessNormFields($channel);
        $this->ensureIndexes($completenessFields);

        $scopables = $this->getScopableAttributes();
        foreach ($scopables as $scopable) {
            $this->ensureIndexesFromAttribute($scopable);
        }
    }

    /**
     * Ensure indexes from potentialy newly activated locale
     *
     * Indexes will be created on the normalizedData part for:
     * - completenesses
     * - localizable attributes
     *
     * @param AbstractAttribute $attribute
     */
    public function ensureIndexesFromLocale(Locale $locale)
    {
        $completenessFields = $this->getCompletenessNormFields(null, $locale);
        $this->ensureIndexes($completenessFields);

        $localizables = $this->getLocalizableAttributes();
        foreach ($localizables as $localizable) {
            $this->ensureIndexesFromAttribute($localizable);
        }
    }

    /**
     * Ensure indexes from potentialy newly activated currency
     *
     * Indexes will be created on the normalizedData part for:
     * - prices (because of potentially added currency)
     *
     * @param Channel $channel
     */
    public function ensureIndexesFromCurrency(Currency $channel)
    {
        $pricesAttributes = $this->getPricesAttributes();
        foreach ($pricesAttributes as $pricesAttribute) {
            $this->ensureIndexesFromAttribute($pricesAttribute);
        }
    }

    /**
     * Get the completeness fields for the channel
     *
     * @param Channel $channel
     *
     * @return array
     */
    protected function getCompletenessNormFields(Channel $channel = null)
    {
        $normFields = array();
        $channels = array();

        if (null === $channel) {
            $channels = $this->getChannels();
        } else {
            $channels[] = $channel;
        }

        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $normFields[] = sprintf(
                    '%s.completenesses.%s-%s',
                    ProductQueryUtility::NORMALIZED_FIELD,
                    $channel->getCode(),
                    $locale->getCode()
                );
            }
        }

        return $normFields;
    }

    /**
     * Get the attribute fields name for normalizedData
     *
     * @param AbstractAttribute $attribute
     *
     * @return string[]
     */
    protected function getAttributeNormFields(AbstractAttribute $attribute)
    {
        $attributeField = ProductQueryUtility::NORMALIZED_FIELD . '.' . $attribute->getCode();
        $fields = [$attributeField];

        if ($attribute->isLocalizable()) {
            $updatedFields = array();
            foreach ($fields as $field) {
                foreach ($this->getLocales() as $locale) {
                    $updatedFields[] = $field.'-'.$locale->getCode();
                }
            }
            $fields = $updatedFields;
        }

        if ($attribute->isScopable()) {
            $updatedFields = array();
            foreach ($fields as $field) {
                foreach ($this->getChannels() as $channel) {
                    $updatedFields[] = $field.'-'.$channel->getCode();
                }
            }
            $fields = $updatedFields;
        }

        return $fields;
    }

    /**
     * Get the attribute fields name for prices
     *
     * @param array             $fields
     * @param AbstractAttribute $attribute
     */
    protected function addFieldsFromPrices(array $fields, AbstractAttribute $attribute)
    {
        $updatedFields = array();

        foreach ($fields as $field) {
            foreach ($this->getCurrencies() as $currency) {
                $updatedFields[] = sprintf(
                    "%s.%s.data",
                    $field,
                    $currency->getCode()
                );
            }
        }

        return $updatedFields;
    }

    /**
     * Get the attribute fields name for option
     *
     * @param array             $fields
     * @param AbstractAttribute $attribute
     */
    protected function addFieldsFromOption(array $fields, AbstractAttribute $attribute)
    {
        $updatedFields = array();

        foreach ($fields as $field) {
            $updatedFields[] = sprintf("%s.id", $field);
        }

        return $updatedFields;
    }

    /**
     * Ensure indexes on the provided field names.
     * Indexed are created in background and the PHP process does not
     * wait for the completion of the index creation (w at 0)
     *
     * @param array $fields
     */
    protected function ensureIndexes(array $fields)
    {
        $collection = $this->getCollection();

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        foreach ($fields as $field) {
            $collection->ensureIndex(
                [ $field => 1 ],
                $indexOptions
            );
        }
    }

    /**
     * Get the MongoDB collection object
     *
     * @return Collection
     */
    protected function getCollection()
    {
        if (null === $this->collection) {
            $documentManager = $this->managerRegistry->getManagerForClass($this->productClass);
            $this->collection = $documentManager->getDocumentCollection($this->productClass);
        }

        return $this->collection;
    }

    /**
     * Get all channels
     *
     * @return array
     */
    protected function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = array();

            $channelManager = $this->managerRegistry->getManagerForClass($this->channelClass);
            $channelRepository = $channelManager->getRepository($this->channelClass);

            $channels = $channelRepository->findAll();

            foreach ($channels as $channel) {
                $this->channels[$channel->getCode()] = $channel;
            }
        }

        return $this->channels;
    }

    /**
     * Get active currencies
     *
     * @return array
     */
    protected function getCurrencies()
    {
        if (null === $this->currencies) {
            $this->currencies = array();

            $currencyManager = $this->managerRegistry->getManagerForClass($this->currencyClass);
            $currencyRepository = $currencyManager->getRepository($this->currencyClass);

            $currencies = $currencyRepository->findBy(['activated' => true]);
            foreach ($currencies as $currency) {
                $this->currencies[$currency->getCode()] = $currency;
            }
        }

        return $this->currencies;
    }

    /**
     * Get active locales
     *
     * @return array
     */
    protected function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = array();

            $localeManager = $this->managerRegistry->getManagerForClass($this->localeClass);
            $localeRepository = $localeManager->getRepository($this->localeClass);

            $locales = $localeRepository->findBy(['activated' => true]);
            foreach ($locales as $locale) {
                $this->locales[$locale->getCode()] = $locale;
            }
        }

        return $this->locales;
    }

    /**
     * Get filterable prices backend type attribute
     *
     * @return array
     */
    protected function getPricesAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['backendType' => 'prices', 'useableAsGridFilter' => true]);

        return $attributes;
    }

    /**
     * Get filterable scopable attributes
     *
     * @return array
     */
    protected function getScopableAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['scopable' => true, 'useableAsGridFilter' => true]);

        return $attributes;
    }

    /**
     * Get filterable localizable attributes
     *
     * @return array
     */
    protected function getLocalizableAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['localizable' => true, 'useableAsGridFilter' => true]);

        return $attributes;
    }
}
