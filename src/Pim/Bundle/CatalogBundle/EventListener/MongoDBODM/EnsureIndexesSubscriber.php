<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Makes sure that the right indexes are set on MongoDB.
 * WARNING: MongoDB allows only 64 indexes on a collection.
 * So we indexed only the filterable attribute and not the sortable-only ones.
 *
 * TODO: remove completenesses indexes on:
 * - locale deactivation
 * - channel removal
 *
 * TODO: remove attribute indexes on:
 * - attribute removal
 *
 * Other cases like:
 * - locale deactivation (for localizable indexes of the attribute)
 * - channel removal (for scopable indexes of the attribute)
 * will not be necessary once the SolR search on the grid is done
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnsureIndexesSubscriber implements EventSubscriber
{
    /** @staticvar string */
    const NORMALIZED_FIELD = 'normalizedData';

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
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate'];
    }

    /**
     * Executed at pre insert time
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->ensureIndexesFromEntity($entity);
    }

    /**
     * Set product normalized data before updating it
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->ensureIndexesFromEntity($entity);
    }

    /**
     * Ensure indexes from entity
     *
     * @param object $entity
     */
    protected function ensureIndexesFromEntity($entity)
    {
        if ($entity instanceof AbstractAttribute) {
            $this->ensureIndexesFromAttribute($entity);
        }

        if ($entity instanceof Channel) {
            $this->ensureIndexesFromChannel($entity);
        }
    }

    /**
     * Ensure indexes from attribute.
     * Indexes will be created on the normalizedData part for attribute
     * that are usable as column and as filter and for identifier and unique attribute
     *
     * @param AbstractAttribute $attribute
     */
    protected function ensureIndexesFromAttribute(AbstractAttribute $attribute)
    {
        if ((AbstractProduct::IDENTIFIER_TYPE === $attribute->getAttributeType())
            || $attribute->isUseableAsGridFilter()
            || $attribute->isUnique()) {

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
    }

    /**
     * Ensure indexes from channel.
     * Indexes will be created on the normalizedData part for:
     * - completenesses (because of potentially new channel or new added locale)
     * - localizable or scopable attributes (idem)
     * - prices (because of potentially added currency)
     *
     * @param AbstractAttribute $attribute
     */
    protected function ensureIndexesFromChannel(Channel $channel)
    {
        $completenessFields = $this->getCompletenessNormFields($channel);
        $this->ensureIndexes($completenessFields);

        $multiValuedAttrs = $this->getMultiValuedAttributes();
        foreach ($multiValuedAttrs as $multiValuedAttr) {
            $this->ensureIndexesFromAttribute($multiValuedAttr);
        }

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
    protected function getCompletenessNormFields(Channel $channel)
    {
        $normFields = array();

        foreach ($channel->getLocales() as $locale) {
            $normFields[] = sprintf(
                '%s.completenesses.%s-%s',
                self::NORMALIZED_FIELD,
                $channel->getCode(),
                $locale->getCode()
            );
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
        $attributeField = self::NORMALIZED_FIELD . '.' . $attribute->getCode();
        $fields = [$attributeField];

        if ($attribute->isLocalizable()) {
            $updatedFields = array();
            foreach ($fields as $field) {
                foreach ($this->getLocales() as $locale) {
                    $updatedFields[] = $attributeField.'-'.$locale->getCode();
                }
            }
            $fields = $updatedFields;
        }

        if ($attribute->isScopable()) {
            $updatedFields = array();
            foreach ($fields as $field) {
                foreach ($this->getChannels() as $channel) {
                    $updatedFields[] = $attributeField.'-'.$channel->getCode();
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
        if (null === $this->collection) {
            $documentManager = $this->managerRegistry->getManagerForClass($this->productClass);
            $this->collection = $documentManager->getDocumentCollection($this->productClass);
        }

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        foreach ($fields as $field) {
            $this->collection->ensureIndex(
                [ $field => 1 ],
                $indexOptions
            );
        }
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

            $currencies = $currencyRepository->findBy(['activated' => 1]);
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

            $locales = $localeRepository->findBy(['activated' => 1]);
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
     * Get filterable scopable or localisable attributes
     *
     * @return array
     */
    protected function getMultiValuedAttributes()
    {
        $attributeManager = $this->managerRegistry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $qb = $attributeRepository->createQueryBuilder('a');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('a.useableAsGridFilter', true),
                $qb->expr()->orx(
                    $qb->expr()->eq('a.localizable', true),
                    $qb->expr()->eq('a.scopable', true)
                )
            )
        );

        $attributes = $qb->getQuery()->getResult();

        return $attributes;
    }
}
