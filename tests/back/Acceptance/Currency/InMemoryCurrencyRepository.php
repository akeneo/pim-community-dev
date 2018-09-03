<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Currency;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;

final class InMemoryCurrencyRepository implements
    SaverInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository,
    CurrencyRepositoryInterface
{
    /** @var Collection */
    private $currencies;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->currencies->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($currency, array $options = [])
    {
        $this->currencies->set($currency->getCode(), $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $currencies = [];
        foreach ($this->currencies as $currency) {
            $keepThisCurrency = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($currency->$getter() !== $value) {
                    $keepThisCurrency = false;
                }
            }

            if ($keepThisCurrency) {
                $currencies[] = $currency;
            }
        }

        return $currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrencies()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrencyCodes()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
