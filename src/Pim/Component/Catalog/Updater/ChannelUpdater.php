<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ChannelInterface;

/**
 * Updates a channel
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $currencyRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $currencyRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'code': 'ecommerce',
     *     "labels": {
     *         "en_US": "Tablet",
     *         "fr_FR": "Tablette"
     *     },
     *     'locales': ['en_US'],
     *     'currencies': ['EUR', 'USD'],
     *     'category_tree': 'master'
     * }
     */
    public function update($channel, array $data, array $options = [])
    {
        if (!$channel instanceof ChannelInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($channel),
                'Pim\Component\Catalog\Model\ChannelInterface'
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($channel, $field, $value);
        }

        return $this;
    }

    /**
     * @param ChannelInterface $channel
     * @param string           $field
     * @param mixed            $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(ChannelInterface $channel, $field, $data)
    {
        switch ($field) {
            case 'code':
                $channel->setCode($data);
                break;
            case 'category_tree':
                $this->setCategoryTree($channel, $data);
                break;
            case 'locales':
                $this->setLocales($channel, $data);
                break;
            case 'currencies':
                $this->setCurrencies($channel, $data);
                break;
            case 'conversion_units':
                $channel->setConversionUnits($data);
                break;
            case 'labels':
                $this->setLabels($channel, $data);
                break;
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param string           $treeCode
     *
     * @throws InvalidPropertyException
     */
    protected function setCategoryTree(ChannelInterface $channel, $treeCode)
    {
        $category = $this->categoryRepository->findOneByIdentifier($treeCode);
        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'category_tree',
                'code',
                'The category does not exist',
                'updater',
                'channel',
                $treeCode
            );
        }
        $channel->setCategory($category);
    }

    /**
     * @param ChannelInterface $channel
     * @param array            $currencyCodes
     *
     * @throws InvalidPropertyException
     */
    protected function setCurrencies(ChannelInterface $channel, array $currencyCodes)
    {
        $currencies = [];
        foreach ($currencyCodes as $currencyCode) {
            $currency = $this->currencyRepository->findOneByIdentifier($currencyCode);
            if (null === $currency) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'currencies',
                    'code',
                    'The currency does not exist',
                    'updater',
                    'channel',
                    $currencyCode
                );
            }

            $currencies[] = $currency;
        }

        $channel->setCurrencies($currencies);
    }

    /**
     * @param ChannelInterface $channel
     * @param array            $localeCodes
     *
     * @throws InvalidPropertyException
     */
    protected function setLocales(ChannelInterface $channel, array $localeCodes)
    {
        $locales = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'locales',
                    'code',
                    'The locale does not exist',
                    'updater',
                    'channel',
                    $localeCode
                );
            }

            $locales[] = $locale;
        }
        $channel->setLocales($locales);
    }

    /**
     * @param ChannelInterface $channel
     * @param array            $data
     */
    protected function setLabels(ChannelInterface $channel, array $data)
    {
        foreach ($data as $localeCode => $label) {
            $channel->setLocale($localeCode);
            $translation = $channel->getTranslation();
            $translation->setLabel($label);
        }
    }
}
