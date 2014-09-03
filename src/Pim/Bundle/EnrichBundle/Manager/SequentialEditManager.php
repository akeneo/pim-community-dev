<?php

namespace Pim\Bundle\EnrichBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param SequentialEditRepository $repository
     * @param SequentialEditFactory    $factory
     */
    public function __construct(
        ObjectManager $om,
        SequentialEditRepository $repository,
        SequentialEditFactory $factory
    ) {
        $this->om         = $om;
        $this->repository = $repository;
        $this->factory    = $factory;
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
}
