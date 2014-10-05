<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Create index for different entity requirements
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexCreator
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var NamingUtility */
    protected $namingUtility;

    /** @var string */
    protected $productClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param NamingUtility   $namingUtility
     * @param string          $productClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        NamingUtility $namingUtility,
        $productClass
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->namingUtility   = $namingUtility;
        $this->productClass    = $productClass;
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
        $attributeFields = $this->namingUtility->getAttributeNormFields($attribute);

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
     * @param ChannelInterface $channel
     */
    public function ensureIndexesFromChannel(ChannelInterface $channel)
    {
        $this->channel = null;

        $completenessFields = $this->getCompletenessNormFields($channel);
        $this->ensureIndexes($completenessFields);

        $scopables = $this->namingUtility->getScopableAttributes();
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
     * @param LocaleInterface $locale
     */
    public function ensureIndexesFromLocale(LocaleInterface $locale)
    {
        $completenessFields = $this->getCompletenessNormFields();
        $this->ensureIndexes($completenessFields);

        $localizables = $this->namingUtility->getLocalizableAttributes();
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
     * @param CurrencyInterface $currency
     */
    public function ensureIndexesFromCurrency(CurrencyInterface $currency)
    {
        $pricesAttributes = $this->namingUtility->getPricesAttributes();
        foreach ($pricesAttributes as $pricesAttribute) {
            $this->ensureIndexesFromAttribute($pricesAttribute);
        }
    }

    /**
     * Get the completeness fields for the channel
     *
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function getCompletenessNormFields(ChannelInterface $channel = null)
    {
        $normFields = [];
        $channels = [];

        if (null === $channel) {
            $channels = $this->namingUtility->getChannels();
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
     * Get the attribute fields name for prices
     *
     * @param array $fields
     *
     * @return array
     */
    protected function addFieldsFromPrices(array $fields)
    {
        $currencyCodes = $this->namingUtility->getCurrencyCodes();
        $updatedFields = $this->namingUtility->appendSuffixes($fields, $currencyCodes, '.');
        $updatedFields = $this->namingUtility->appendSuffixes($fields, ['data'], '.');

        return $updatedFields;
    }

    /**
     * Get the attribute fields name for option
     *
     * @param array $fields
     *
     * @return array
     */
    protected function addFieldsFromOption(array $fields)
    {
        $updatedFields = $this->namingUtility->appendSuffixes($fields, ['id'], '.');

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
                [$field => 1],
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
        $documentManager = $this->managerRegistry->getManagerForClass($this->productClass);

        return $documentManager->getDocumentCollection($this->productClass);
    }
}
