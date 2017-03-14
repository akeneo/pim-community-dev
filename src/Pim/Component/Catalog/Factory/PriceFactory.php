<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;

/**
 * Creates and configures a price instance.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceFactory
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var string */
    protected $priceClass;

    /**
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param string                      $priceClass
     */
    public function __construct(CurrencyRepositoryInterface $currencyRepository, $priceClass)
    {
        $this->currencyRepository = $currencyRepository;
        $this->priceClass = $priceClass;
    }

    /**
     * This method creates a price instance, after checking the provided currency
     * exists.
     * Amount and currency are directly set during price instantiation.
     *
     * @param float  $amount
     * @param string $currency
     *
     * @throws \InvalidArgumentException
     * @return ProductPriceInterface
     */
    public function createPrice($amount, $currency)
    {
        if (null === $this->currencyRepository->findOneByIdentifier($currency)) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'currency',
                'code',
                'The currency does not exist',
                static::class,
                $currency
            );
        }

        if ('' === $amount) {
            $amount = null;
        }

        $price = new $this->priceClass($amount, $currency);

        return $price;
    }
}
