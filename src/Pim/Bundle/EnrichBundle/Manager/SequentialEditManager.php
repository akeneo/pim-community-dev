<?php

namespace Pim\Bundle\EnrichBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Sequential edit manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditManager
{
    /** @var SequentialEditRepository */
    protected $repository;

    /** @var SequentialEditFactory */
    protected $factory;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * Constructor
     *
     * @param SequentialEditRepository   $repository
     * @param SequentialEditFactory      $factory
     * @param ProductRepositoryInterface $productRepository
     * @param RemoverInterface           $remover
     */
    public function __construct(
        SequentialEditRepository $repository,
        SequentialEditFactory $factory,
        ProductRepositoryInterface $productRepository,
        RemoverInterface $remover
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->productRepository = $productRepository;
        $this->remover = $remover;
    }

    /**
     * Returns a sequential edit entity
     *
     * @param array         $objectSet
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function createEntity(array $objectSet, UserInterface $user)
    {
        return $this->factory->create($objectSet, $user);
    }

    /**
     * Remove a sequential edit for a specific user
     *
     * @param UserInterface $user
     */
    public function removeByUser(UserInterface $user)
    {
        $sequentialEdit = $this->findByUser($user);
        if (null !== $sequentialEdit) {
            $this->remover->remove($sequentialEdit);
        }
    }

    /**
     * Find a SequentialEdit entity from a specific User
     *
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function findByUser(UserInterface $user)
    {
        return $this->repository->findOneBy(['user' => $user]);
    }

    /**
     * Find wrapped products from a product
     *
     * @param SequentialEdit   $sequentialEdit
     * @param ProductInterface $product
     */
    public function findWrap(SequentialEdit $sequentialEdit, ProductInterface $product)
    {
        $objectSet = $sequentialEdit->getObjectSet();
        $currentKey = array_search($product->getId(), $objectSet);

        $previous = $this->findPrevious($sequentialEdit, $currentKey);
        $next = $this->findNext($sequentialEdit, $currentKey);

        $sequentialEdit->setCurrent($product);
        $sequentialEdit->setPrevious($previous);
        $sequentialEdit->setNext($next);
    }

    /**
     * Find next sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     * @param int            $currentKey
     *
     * @return null|ProductInterface
     */
    protected function findNext(SequentialEdit $sequentialEdit, $currentKey)
    {
        $next = null;
        $objectSet = $sequentialEdit->getObjectSet();
        $productCount = $sequentialEdit->countObjectSet();
        while (++$currentKey < $productCount && null === $next) {
            $next = $this->productRepository->find($objectSet[$currentKey]);
        }

        return $next;
    }

    /**
     * Find previous sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     * @param int            $currentKey
     *
     * @return null|ProductInterface
     */
    protected function findPrevious(SequentialEdit $sequentialEdit, $currentKey)
    {
        $previous = null;
        $objectSet = $sequentialEdit->getObjectSet();
        while ($currentKey-- > 0 && null === $previous) {
            $previous = $this->productRepository->find($objectSet[$currentKey]);
        }

        return $previous;
    }
}
