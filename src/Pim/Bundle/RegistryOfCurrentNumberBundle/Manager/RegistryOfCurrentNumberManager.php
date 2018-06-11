<?php
namespace Pim\Bundle\RegistryOfCurrentNumberBundle\Manager;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\RegistryOfCurrentNumberBundle\Entity\RegistryOfCurrentNumber;

/**
 * RegistryOfCurrentNumber manager
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegistryOfCurrentNumberManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var string */
    private $class;

    /** @var string */
    private $code;

    /**
     * RegistryOfCurrentNumberManager constructor.
     * @param EntityManager $entityManager
     * @param $class
     * @param $code
     */
    public function __construct(EntityManager $entityManager, $class, $code)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
        $this->code = $code;
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function increment()
    {
        $registry = $this->findOrCreateRegistryOfCurrentNumber();

        $registry->setValue((int) $registry->getValue()+1);
        $this->entityManager->persist($registry);
        $this->entityManager->flush();

        return $registry->getValue();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function decrement()
    {
        $registry = $this->findOrCreateRegistryOfCurrentNumber();

        $registry->setValue((int) $registry->getValue()-1);
        $this->entityManager->persist($registry);
        $this->entityManager->flush();

        return $registry->getValue();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function countAllRegistryOfCurrentNumbers()
    {
        $count = $this->entityManager->getRepository($this->class)->countAll();

        $registry = $this->findOrCreateRegistryOfCurrentNumber();

        $registry->setValue((int) $count);
        $this->entityManager->persist($registry);
        $this->entityManager->flush();

        return $registry->getValue();
    }

    /**
     * @return RegistryOfCurrentNumber
     */
    private function findOrCreateRegistryOfCurrentNumber()
    {
        $registry = $this->entityManager->getRepository(RegistryOfCurrentNumber::class)->findOneByCode($this->code);

        if (is_null($registry)) {
            $registry = new RegistryOfCurrentNumber();
            $registry->setCode($this->code);
        }
        return $registry;
    }
}
