<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollection;

/**
 * Factory that creates price collection product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var PriceFactory */
    protected $priceFactory;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param PriceFactory $priceFactory
     * @param string       $productValueClass
     * @param string       $supportedAttributeType
     */
    public function __construct(PriceFactory $priceFactory, $productValueClass, $supportedAttributeType)
    {
        $this->priceFactory = $priceFactory;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass($attribute, $channelCode, $localeCode, $this->getPrices($data));

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if data are valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'prices collection',
                'factory',
                gettype($data)
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'prices collection',
                    'factory',
                    gettype($data).' of '.gettype($price)
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    'prices collection',
                    'factory',
                    implode(', ', array_keys($price))
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'prices collection',
                    'factory',
                    implode(', ', array_keys($price))
                );
            }
        }
    }

    /**
     * Gets a collection of price from prices in standard format.
     *
     * @param array $data
     *
     * @return PriceCollection
     */
    protected function getPrices(array $data)
    {
        $prices = new PriceCollection();

        foreach ($data as $price) {
            $prices->add($this->priceFactory->createPrice($price['amount'], $price['currency']));
        }

        return $prices;
    }
}
