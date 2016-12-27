<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Factory that creates empty product values
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueFactory
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var string */
    private $productValueClass;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param string                     $productValueClass
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        $productValueClass
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
    }

    /**
     * This method effectively creates an empty product value while checking the provided localeCode and ChannelCode
     * exists.
     * The Data for this product value should be set in a second time using ProductValue::setData method.
     *
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     * @param string             $localeCode
     *
     * @return ProductValueInterface
     *
     */
    public function createEmpty(AttributeInterface $attribute, $channelCode, $localeCode)
    {
        if ($attribute->isScopable() && null === $this->channelRepository->findOneByIdentifier($channelCode)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A scope must be provided to create a value for the scopable attribute %s',
                    $attribute->getCode()
                )
            );
        }

        if ($attribute->isLocalizable() && null === $this->localeRepository->findOneByIdentifier($localeCode)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A locale must be provided to create a value for the localizable attribute %s',
                    $attribute->getCode()
                )
            );
        }

        /** @var ProductValueInterface $value */
        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        return $value;
    }
}
