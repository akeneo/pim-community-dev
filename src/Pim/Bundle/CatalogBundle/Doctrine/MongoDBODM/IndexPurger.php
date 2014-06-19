<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Makes sure that the indexes links to entity are removed.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexPurger
{
    /** @staticvar string */
    const NORMALIZED_FIELD = 'normalizedData';

    /** @var Collection */
    protected $collection;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $productClass;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $productClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->productClass    = $productClass;
    }

    /**
     * Remove indexes associated with the provided locale
     *
     * @param Locale $locale
     */
    public function purgeIndexesFromLocale(Locale $locale)
    {
        $localePattern = sprintf(
            '/^%s\..+-%s/',
            ProductQueryUtility::NORMALIZED_FIELD,
            $locale->getCode()
        );

        $indexesToRemove = $this->getIndexesMatching($localePattern);

        $this->removeIndexes($indexesToRemove);
    }

    /**
     * Remove indexes associated with the provided channel
     *
     * @param Channel $channel
     */
    public function purgeIndexesFromChannel(Channel $channel)
    {
        $channelPattern = sprintf(
            '/^%s\..+-%s/',
            ProductQueryUtility::NORMALIZED_FIELD,
            $channel->getCode()
        );

        $indexesToRemove = $this->getIndexesMatching($channelPattern);

        $this->removeIndexes($indexesToRemove);
    }

    /**
     * Remove indexes associated with the provided currency
     *
     * @param Currency $currency
     */
    public function purgeIndexesFromCurrency(Currency $currency)
    {
        $currencyPattern = sprintf(
            '/%s\..+\.%s\.data/',
            ProductQueryUtility::NORMALIZED_FIELD,
            $currency->getCode()
        );

        $indexesToRemove = $this->getIndexesMatching($currencyPattern);

        $this->removeIndexes($indexesToRemove);
    }

    /**
     * Remove indexes associated with the provided attribute
     *
     * @param AbstractAttribute $attribute
     */
    public function purgeIndexesFromAttribute(AbstractAttribute $attribute)
    {
        $attributePattern = sprintf(
            '/^%s\.%s([\.-].+)?$/',
            ProductQueryUtility::NORMALIZED_FIELD,
            $attribute->getCode()
        );

        $indexesToRemove = $this->getIndexesMatching($attributePattern);

        $this->removeIndexes($indexesToRemove);
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
     * Get indexes names that contains the specified string
     *
     * @param string $pattern
     *
     * @return array
     */
    protected function getIndexesMatching($pattern)
    {
        $collection = $this->getCollection();

        $indexes = $collection->getIndexInfo();
        $matchingIndexes = [];

        foreach ($indexes as $index) {
            $indexKeys = array_keys($index['key']);
            $key = reset($indexKeys);
            if (0 !== preg_match($pattern, $key)) {
                $matchingIndexes[] = $key;
            }
        }

        return $matchingIndexes;
    }

    /**
     * Remove indexes with names provided in the array parameter
     *
     * @oaram array $indexes
     */
    protected function removeIndexes(array $indexes)
    {
        $collection = $this->getCollection();

        foreach ($indexes as $key) {
            $collection->deleteIndex($key);
        }
    }
}
