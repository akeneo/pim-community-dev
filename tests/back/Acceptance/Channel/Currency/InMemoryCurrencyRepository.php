<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel\Currency;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\CurrencyInterface;

final class InMemoryCurrencyRepository implements
    SaverInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
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
        if(!$currency instanceof CurrencyInterface) {
            throw new \InvalidArgumentException('Only currency objects are supported.');
        }

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
}
