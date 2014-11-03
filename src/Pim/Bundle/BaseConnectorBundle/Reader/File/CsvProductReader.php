<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

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
 */
class CsvProductReader extends CsvReader
{
    /** @var array Media attribute codes */
    protected $mediaAttributes = array();

    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /** @var ChannelRepository */
    protected $channelRepository;

    /** @var LocaleRepository */
    protected $localeRepository;

    /** @var CurrencyRepository */
    protected $currencyRepository;

    /**
     * Constructor
     *
     * @param EntityManager    $entityManager
     * @param FieldNameBuilder $fieldNameBuilder,
     * @param string           $attributeClass
     * @param string           $channelClass
     * @param string           $localeClass
     * @param string           $currencyClass
     */
    public function __construct(
        EntityManager $entityManager,
        FieldNameBuilder $fieldNameBuilder,
        $attributeClass,
        $channelClass,
        $localeClass,
        $currencyClass
    ) {
        $this->fieldNameBuilder = $fieldNameBuilder;

        /** @var AttributeRepository $attributeRepository */
        $attributeRepository = $entityManager->getRepository($attributeClass);
        $this->mediaAttributes = $attributeRepository->findMediaAttributeCodes();

        $this->channelRepository = $entityManager->getRepository($channelClass);
        $this->localeRepository = $entityManager->getRepository($localeClass);
        $this->currencyRepository = $entityManager->getRepository($currencyClass);
    }

    /**
     * Set the media attributes
     *
     * @param array $mediaAttributes
     *
     * @return CsvProductReader
     */
    public function setMediaAttributes(array $mediaAttributes)
    {
        $this->mediaAttributes = $mediaAttributes;

        return $this;
    }

    /**
     * Get the media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
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

            if (in_array($attributeCode, $this->mediaAttributes)) {
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
            if (null !== $info = $this->fieldNameBuilder->extractAttributeFieldNameInfos($fieldName)) {
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
