<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

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
     *     'label': 'Ecommerce',
     *     'locales': ['en_US'],
     *     'currencies': ['EUR', 'USD'],
     *     'tree': 'master',
     *     'color': 'orange'
     * }
     */
    public function update($channel, array $data, array $options = [])
    {
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\ChannelInterface", "%s" provided.',
                    ClassUtils::getClass($channel)
                )
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
     * @throws \InvalidArgumentException
     */
    protected function setData(ChannelInterface $channel, $field, $data)
    {
        if ('code' === $field) {
            $channel->setCode($data);
        } elseif ('tree' === $field) {
            $category = $this->findCategory($data);
            if (null !== $category) {
                $channel->setCategory($category);
            } else {
                throw new \InvalidArgumentException(sprintf('Category with "%s" code does not exist', $data));
            }
        } elseif ('locales' === $field) {
            foreach ($data as $localeCode) {
                $locale = $this->findLocale($localeCode);
                if (null !== $locale) {
                    $channel->addLocale($locale);
                } else {
                    throw new \InvalidArgumentException(sprintf('Locale with "%s" code does not exist', $localeCode));
                }
            }
        } elseif ('currencies' === $field) {
            foreach ($data as $currencyCode) {
                $currency = $this->findCurrency($currencyCode);
                if (null !== $currency) {
                    $channel->addCurrency($currency);
                } else {
                    throw new \InvalidArgumentException(sprintf('Currency with "%s" code does not exist', $currencyCode));
                }
            }
        } elseif ('label' === $field) {
            $channel->setLabel($data);
        } elseif ('color' === $field) {
            $channel->setColor($data);
        }
    }

    /**
     * @param string $code
     *
     * @return CategoryInterface|null
     */
    protected function findCategory($code)
    {
        return $this->categoryRepository->findOneByIdentifier($code);
    }

    /**
     * @param string $code
     *
     * @return LocaleInterface|null
     */
    protected function findLocale($code)
    {
        return $this->localeRepository->findOneByIdentifier($code);
    }

    /**
     * @param string $code
     *
     * @return CurrencyInterface|null
     */
    protected function findCurrency($code)
    {
        return $this->currencyRepository->findOneByIdentifier($code);
    }
}
