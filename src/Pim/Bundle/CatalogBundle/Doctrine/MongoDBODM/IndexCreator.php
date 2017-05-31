<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Create index for different entity requirements
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexCreator
{
    /** @staticvar int 64 is the MongoDB limit for indexes (not configurable) */
    const MONGODB_INDEXES_LIMIT = 64;

    /** @staticvar int the default MongoDB index type */
    const ASCENDANT_INDEX_TYPE = 1;

    /** @staticvar string the hash MongoDB index type */
    const HASHED_INDEX_TYPE = 'hashed';

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var NamingUtility */
    protected $namingUtility;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $attributeClass;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param NamingUtility   $namingUtility
     * @param string          $productClass
     * @param LoggerInterface $logger
     * @param string          $attributeClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        NamingUtility $namingUtility,
        $productClass,
        LoggerInterface $logger,
        $attributeClass
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->namingUtility = $namingUtility;
        $this->productClass = $productClass;
        $this->logger = $logger;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Ensure indexes from attribute.
     * Indexes will be created on the normalizedData part for attribute
     * that are usable as column and as filter and for identifier and unique attribute
     *
     * @param AttributeInterface $attribute
     */
    public function ensureIndexesFromAttribute(AttributeInterface $attribute)
    {
        $attributeFields = $this->namingUtility->getAttributeNormFields($attribute);
        switch ($attribute->getBackendType()) {
            case AttributeTypes::BACKEND_TYPE_PRICE:
                $attributeFields = $this->addFieldsFromPrices($attributeFields);
                break;
            case AttributeTypes::BACKEND_TYPE_OPTION:
            case AttributeTypes::BACKEND_TYPE_OPTIONS:
                $attributeFields = $this->addFieldsFromOption($attributeFields);
                break;
        }

        $indexType = $this->getIndexTypeFromAttribute($attribute);
        $this->ensureIndexes($attributeFields, $indexType);
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
        $completenessFields = $this->getCompletenessNormFields($channel);
        $this->ensureIndexes($completenessFields);

        $scopables = $this->namingUtility->getScopableAttributes();
        foreach ($scopables as $scopable) {
            $indexType = $this->getIndexTypeFromAttribute($scopable);
            $this->ensureIndexesFromAttribute($scopable, $indexType);
        }
    }

    /**
     * Ensure indexes from potentially newly activated locale
     *
     * Indexes will be created on the normalizedData part for:
     * - completenesses
     * - localizable attributes
     */
    public function ensureIndexesFromLocale()
    {
        $completenessFields = $this->getCompletenessNormFields();
        $this->ensureIndexes($completenessFields);

        $localizables = $this->namingUtility->getLocalizableAttributes();
        foreach ($localizables as $localizable) {
            $indexType = $this->getIndexTypeFromAttribute($localizable);
            $this->ensureIndexesFromAttribute($localizable, $indexType);
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
    public function ensureIndexesFromCurrency()
    {
        $pricesAttributes = $this->namingUtility->getPricesAttributes();
        foreach ($pricesAttributes as $pricesAttribute) {
            $indexType = $this->getIndexTypeFromAttribute($pricesAttribute);
            $this->ensureIndexesFromAttribute($pricesAttribute, $indexType);
        }
    }

    /**
     * Ensure indexes for completeness
     */
    public function ensureCompletenessesIndexes()
    {
        $completenessFields = $this->getCompletenessNormFields();
        $this->ensureIndexes($completenessFields);
    }

    /**
     * Ensure indexes for unique attributes
     */
    public function ensureUniqueAttributesIndexes()
    {
        $attributes = $this->getAttributeRepository()->findBy(
            ['unique'  => true],
            ['created' => 'ASC'],
            self::MONGODB_INDEXES_LIMIT
        );

        foreach ($attributes as $attribute) {
            $indexType = $this->getIndexTypeFromAttribute($attribute);
            $this->ensureIndexesFromAttribute($attribute, $indexType);
        }
    }

    /**
     * Ensure indexes for attributes
     */
    public function ensureAttributesIndexes()
    {
        $attributes = $this->getAttributeRepository()->findBy(
            ['useableAsGridFilter' => true],
            ['created'             => 'ASC'],
            self::MONGODB_INDEXES_LIMIT
        );

        foreach ($attributes as $attribute) {
            $indexType = $this->getIndexTypeFromAttribute($attribute);
            $this->ensureIndexesFromAttribute($attribute, $indexType);
        }
    }

    /**
     * @return AttributeRepositoryInterface
     */
    protected function getAttributeRepository()
    {
        return $this->managerRegistry->getRepository($this->attributeClass);
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
        $updatedFields = $this->namingUtility->appendSuffixes($updatedFields, ['data'], '.');

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
     *
     * Indexes are created in background and the PHP process does not wait for the completion of the index creation
     * (w at 0)
     *
     * Index can be ascendant (1 by default) or "hashed" in case of long text to avoid the index key limit issue
     * enforced in Mongo 2.6 (cf https://docs.mongodb.com/manual/reference/limits/#Index-Key-Limit)
     *
     * @param array      $fields
     * @param int|string $indexType
     */
    protected function ensureIndexes(array $fields, $indexType = self::ASCENDANT_INDEX_TYPE)
    {
        $collection = $this->getCollection();
        $preNbIndexes = count($collection->getIndexInfo());
        $postNbIndexes = $preNbIndexes + count($fields);
        if ($postNbIndexes > self::MONGODB_INDEXES_LIMIT) {
            $msg = sprintf('Too many MongoDB indexes (%d), no way to add %s', $preNbIndexes, print_r($fields, true));
            $this->logger->error($msg);

            return;
        }

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        foreach ($fields as $field) {
            $collection->ensureIndex(
                [$field => $indexType],
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

    /**
     * @param AttributeInterface $attribute
     *
     * @return int|string
     */
    protected function getIndexTypeFromAttribute(AttributeInterface $attribute)
    {
        return (AttributeTypes::BACKEND_TYPE_TEXTAREA === $attribute->getBackendType()) ?
            self::HASHED_INDEX_TYPE : self::ASCENDANT_INDEX_TYPE;
    }
}
