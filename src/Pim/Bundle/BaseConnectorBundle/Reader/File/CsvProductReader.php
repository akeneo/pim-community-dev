<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;

/**
 * Product csv reader
 *
 * This specialized csv reader exists because, as the product are bulk inserted,
 * we cannot rely on the UniqueValueValidator which rely on data present inside the database.
 * Its second purpose is to replace relative media path to absolute path, in order for later
 * process to know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be remove in 1.5, please use Pim\Component\Connector\Reader\File\CsvProductReader, btw the logic
 *             of checkAttributesInHeader is now handled by the AttributeColumnInfoExtractor
 */
class CsvProductReader extends CsvReader
{
    /** @var string[] Media attribute codes */
    protected $mediaAttributes;

    /** @var AttributeColumnInfoExtractor */
    protected $fieldExtractor;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param EntityManager                $entityManager
     * @param AttributeColumnInfoExtractor $fieldExtractor
     * @param string                       $attributeClass
     * @param string                       $channelClass
     * @param string                       $localeClass
     * @param string                       $currencyClass
     */
    public function __construct(
        EntityManager $entityManager,
        AttributeColumnInfoExtractor $fieldExtractor,
        $attributeClass,
        $channelClass,
        $localeClass,
        $currencyClass
    ) {
        $this->fieldExtractor      = $fieldExtractor;
        $this->attributeRepository = $entityManager->getRepository($attributeClass);
        $this->channelRepository   = $entityManager->getRepository($channelClass);
        $this->localeRepository    = $entityManager->getRepository($localeClass);
        $this->currencyRepository  = $entityManager->getRepository($currencyClass);
    }

    /**
     * Set the media attributes
     *
     * @param array|null $mediaAttributes
     *
     * @return CsvProductReader
     */
    public function setMediaAttributes($mediaAttributes)
    {
        $this->mediaAttributes = $mediaAttributes;

        return $this;
    }

    /**
     * Get the media attributes
     *
     * @return string[]
     */
    public function getMediaAttributes()
    {
        if (null === $this->mediaAttributes) {
            $this->mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
        }

        return $this->mediaAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'mediaAttributes' => [
                    'system' => true
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data)) {
            return $data;
        }

        return $this->transformMediaPathToAbsolute($data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        foreach ($data as $code => $value) {
            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;
            $value = trim($value);

            if (in_array($attributeCode, $this->getMediaAttributes()) && !empty($value)) {
                $data[$code] = dirname($this->filePath) . '/' . $value;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeRead()
    {
        parent::initializeRead();

        $this->checkAttributesInHeader();
    }

    /**
     * Checks that attributes in the header have existing locale, scope and currency.
     *
     * @throws \LogicException
     */
    protected function checkAttributesInHeader()
    {
        $channels = $this->channelRepository->getChannelCodes();
        $locales = $this->localeRepository->getActivatedLocaleCodes();
        $currencies = $this->currencyRepository->getActivatedCurrencyCodes();

        foreach ($this->fieldNames as $fieldName) {
            if (null !== $info = $this->fieldExtractor->extractColumnInfo($fieldName)) {
                $locale = $info['locale_code'];
                $channel = $info['scope_code'];
                $currency = isset($info['price_currency']) ? $info['price_currency'] : null;

                if (null !== $locale && !in_array($locale, $locales)) {
                    throw new \LogicException(sprintf('Locale %s does not exist.', $locale));
                }
                if (null !== $channel && !in_array($channel, $channels)) {
                    throw new \LogicException(sprintf('Channel %s does not exist.', $channel));
                }
                if (null !== $currency && !in_array($currency, $currencies)) {
                    throw new \LogicException(sprintf('Currency %s does not exist.', $currency));
                }
            }
        }
    }
}
