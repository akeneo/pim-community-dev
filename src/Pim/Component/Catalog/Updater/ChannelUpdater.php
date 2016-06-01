<?php

namespace Pim\Component\Catalog\Updater;

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
        $this->localeRepository   = $localeRepository;
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
     *     'tree': 'master'
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
        switch ($field) {
            case 'code':
                $channel->setCode($data);
                break;
            case 'tree':
                $category = $this->categoryRepository->findOneByIdentifier($data);
                if (null === $category) {
                    throw new \InvalidArgumentException(sprintf('Category with "%s" code does not exist', $data));
                }
                $channel->setCategory($category);
                break;
            case 'locales':
                foreach ($data as $localeCode) {
                    $locale = $this->localeRepository->findOneByIdentifier($localeCode);
                    if (null === $locale) {
                        throw new \InvalidArgumentException(sprintf(
                            'Locale with "%s" code does not exist',
                            $localeCode
                        ));
                    }
                    $channel->addLocale($locale);
                }
                break;
            case 'currencies':
                foreach ($data as $currencyCode) {
                    $currency = $this->currencyRepository->findOneByIdentifier($currencyCode);
                    if (null === $currency) {
                        throw new \InvalidArgumentException(sprintf(
                            'Currency with "%s" code does not exist',
                            $currencyCode
                        ));
                    }
                    $channel->addCurrency($currency);
                }
                break;
            case 'label':
                $channel->setLabel($data);
                break;
        }
    }
}
