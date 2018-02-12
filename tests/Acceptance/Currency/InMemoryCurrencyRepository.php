<?php


namespace Akeneo\Test\Acceptance\Currency;


use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;

class InMemoryCurrencyRepository implements
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

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($code)
    {
        return $this->currencies->get($code);
    }

    public function save($currency, array $options = [])
    {
        $this->currencies->set($currency->getCode(), $currency);
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $currencies = [];
        foreach ($this->currencies as $locale) {
            foreach ($criteria as $key => $value) {
                $getter = 'get' . ucfirst($key);
                if ($locale->$getter() === $value) {
                    $currencies[$locale] = $locale;
                }
            }
        }

        return $currencies;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object|null The object.
     */
    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }
}
