<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Category;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;

final class InMemoryCategoryRepository implements
    IdentifiableObjectRepositoryInterface,
    SaverInterface,
    ObjectRepository
{
    /** @var Collection */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
        return $this->categories->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($category, array $options = [])
    {
        $this->categories->set($category->getCode(), $category);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $categories = [];
        foreach ($this->categories as $category) {
            $keepThisCategory = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($category->$getter() !== $value) {
                    $keepThisCategory = false;
                }
            }

            if ($keepThisCategory) {
                $categories[] = $category;
            }
        }

        return $categories;
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
