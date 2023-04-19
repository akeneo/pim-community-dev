<?php

namespace Akeneo\Channel\Infrastructure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a channel
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelUpdater implements ObjectUpdaterInterface
{
    public function __construct(
        protected IdentifiableObjectRepositoryInterface $categoryRepository,
        protected IdentifiableObjectRepositoryInterface $localeRepository,
        protected IdentifiableObjectRepositoryInterface $currencyRepository,
        protected IdentifiableObjectRepositoryInterface $attributeRepository,
        protected TranslatableUpdater $translatableUpdater
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code' => 'ecommerce',
     *     'labels' => [
     *         'en_US' => 'Tablet',
     *         'fr_FR' => 'Tablette'
     *     ],
     *     'locales' => ['en_US'],
     *     'currencies' => ['EUR', 'USD'],
     *     'conversion_units' => [ 'weight' => 'GRAM', 'display_diagonal' => 'METER' ],
     *     'category_tree' => 'master'
     * ]
     */
    public function update($channel, array $data, array $options = []): self
    {
        if (!$channel instanceof ChannelInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($channel),
                ChannelInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($channel, $field, $value);
        }

        return $this;
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, $data): void
    {
        if (in_array($field, ['labels', 'locales', 'currencies', 'conversion_units'])) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array($field, ['code', 'category_tree'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param string           $field
     * @param mixed            $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(ChannelInterface $channel, $field, $data): void
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
     * set labels on a channel, ensuring correct case of locale codes.
     * @param ChannelInterface $channel
     * @param array            $localizedLabels
     */
    private function setLabels($channel, $localizedLabels)
    {
        // using known locale code when found (modulo case-insensitive comparison)
        // leaving unknown locale code as is for the moment (Jira PIM-10372)
        $normalizedLocalizedLabels = [];
        foreach ($localizedLabels as $localeCode => $label) {
            $knownLocale = $this->localeRepository->findOneByIdentifier($localeCode);
            $normalizedLocalCode = null === $knownLocale ? $localeCode : $knownLocale->getCode();
            $normalizedLocalizedLabels[$normalizedLocalCode] = $label;
        }

        $this->translatableUpdater->update($channel, $normalizedLocalizedLabels);
    }

    /**
     * @param ChannelInterface $channel
     * @param string           $treeCode
     *
     * @throws InvalidPropertyException
     */
    protected function setCategoryTree(ChannelInterface $channel, $treeCode): void
    {
        $category = $this->categoryRepository->findOneByIdentifier($treeCode);
        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'category_tree',
                'code',
                'The category does not exist',
                static::class,
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
    protected function setCurrencies(ChannelInterface $channel, array $currencyCodes): void
    {
        $currencies = [];
        foreach ($currencyCodes as $currencyCode) {
            $currency = $this->currencyRepository->findOneByIdentifier($currencyCode);
            if (null === $currency) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'currencies',
                    'code',
                    'The currency does not exist',
                    static::class,
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
    protected function setLocales(ChannelInterface $channel, array $localeCodes): void
    {
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'locales',
                    'code',
                    'The locale does not exist',
                    static::class,
                    $localeCode
                );
            }

            $channel->addLocale($locale);
        }

        foreach ($channel->getLocales() as $locale) {
            if (!in_array($locale->getCode(), $localeCodes)) {
                $channel->removeLocale($locale);
            }
        }
    }
}
