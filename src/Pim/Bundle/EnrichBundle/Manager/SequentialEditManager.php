<?php

namespace Pim\Bundle\EnrichBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory;
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
    /** @var ObjectManager */
    protected $om;

    /** @var SequentialEditRepository */
    protected $repository;

    /** @var SequentialEditFactory */
    protected $factory;

    /** @var ProductManager */
    protected $productManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param SequentialEditRepository $repository
     * @param SequentialEditFactory    $factory
     * @param ProductManager           $productManager
     */
    public function __construct(
        ObjectManager $om,
        SequentialEditRepository $repository,
        SequentialEditFactory $factory,
        ProductManager $productManager
    ) {
        $this->om             = $om;
        $this->repository     = $repository;
        $this->factory        = $factory;
        $this->productManager = $productManager;
    }

    /**
     * Save a sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     */
    public function save(SequentialEdit $sequentialEdit)
    {
        $this->om->persist($sequentialEdit);
        $this->om->flush();
    }

    /**
     * Returns a sequential edit entity
     *
     * @param array         $productSet
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function createEntity(array $productSet, UserInterface $user)
    {
        return $this->factory->create($productSet, $user);
    }

    /**
     * Remove a sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     */
    public function remove(SequentialEdit $sequentialEdit)
    {
        $this->om->remove($sequentialEdit);
        $this->om->flush();
    }

    /**
     * Remove a sequential edit for a specific user
     *
     * @param UserInterface $user
     */
    public function removeFromUser(UserInterface $user)
    {
        $sequentialEdit = $this->findByUser($user);
        if ($sequentialEdit) {
            $this->remove($sequentialEdit);
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
        $productSet = $sequentialEdit->getProductSet();
        $currentKey = array_search($product->getId(), $productSet);

        $previous = $this->findPrevious($sequentialEdit, $currentKey);
        $next     = $this->findNext($sequentialEdit, $currentKey);

        $sequentialEdit->setCurrent($product);
        $sequentialEdit->setPrevious($previous);
        $sequentialEdit->setNext($next);
    }

    /**
     * Find next sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     * @param integer        $currentKey
     *
     * @return null|ProductInterface
     */
    protected function findNext(SequentialEdit $sequentialEdit, $currentKey)
    {
        $productSet = $sequentialEdit->getProductSet();
        $productCount = $sequentialEdit->countProductSet();
        while (++$currentKey < $productCount) {
            $next = $this->productManager->find($productSet[$currentKey]);
            if (null !== $next) {
                return $next;
            }
        }

        return null;
    }

    /**
     * Find previous sequential edit entity
     *
     * @param SequentialEdit $sequentialEdit
     * @param integer        $currentKey
     *
     * @return null|ProductInterface
     */
    protected function findPrevious(SequentialEdit $sequentialEdit, $currentKey)
    {
        $productSet = $sequentialEdit->getProductSet();
        while ($currentKey-- > 0) {
            $previous = $this->productManager->find($productSet[$currentKey]);
            if (null !== $previous) {
                return $previous;
            }
        }

        return null;
    }
}
