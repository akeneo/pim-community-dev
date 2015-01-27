<?php

namespace Pim\Bundle\EnrichBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
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
class SequentialEditManager implements SaverInterface, RemoverInterface
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
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof SequentialEdit) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\EnrichBundle\Entity\SequentialEdit, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->om->persist($object);
        $this->om->flush($object);
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
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof SequentialEdit) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\EnrichBundle\Entity\SequentialEdit, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->om->remove($object);
        $this->om->flush($object);
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
        $objectSet = $sequentialEdit->getObjectSet();
        $currentKey = array_search($product->getId(), $objectSet);

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
        $next = null;
        $objectSet = $sequentialEdit->getObjectSet();
        $productCount = $sequentialEdit->countObjectSet();
        while (++$currentKey < $productCount && null === $next) {
            $next = $this->productManager->find($objectSet[$currentKey]);
        }

        return $next;
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
        $previous = null;
        $objectSet = $sequentialEdit->getObjectSet();
        while ($currentKey-- > 0 && null === $previous) {
            $previous = $this->productManager->find($objectSet[$currentKey]);
        }

        return $previous;
    }
}
