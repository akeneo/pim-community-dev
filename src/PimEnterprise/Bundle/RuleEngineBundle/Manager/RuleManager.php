<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RuleManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleManager implements SaverInterface, RemoverInterface
{
    /** @var RuleDefinitionRepositoryInterface */
    protected $repository;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param RuleDefinitionRepositoryInterface $repository
     * @param EntityManager                     $entityManager
     * @param EventDispatcherInterface          $eventDispatcher
     */
    public function __construct(
        RuleDefinitionRepositoryInterface $repository,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository      = $repository;
        $this->entityManager   = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof RuleInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use  PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE, new RuleEvent($object));

        $options = array_merge(['flush' => true], $options);
        $this->entityManager->persist($object);
        if (true === $options['flush']) {
            $this->entityManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE, new RuleEvent($object));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($rule, array $options = [])
    {
        if (!$rule instanceof RuleInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use  PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface, "%s" provided',
                    ClassUtils::getClass($rule)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->entityManager->remove($rule);
        if (true === $options['flush']) {
            $this->entityManager->flush();
        }
    }
}
