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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RuleManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleManager implements SaverInterface, RemoverInterface
{
    /** @var RuleRepositoryInterface */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param RuleRepositoryInterface  $repository
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        RuleRepositoryInterface $repository,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository      = $repository;
        $this->objectManager   = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @{@inheritdoc}
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
        $this->objectManager->persist($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE, new RuleEvent($object));
    }

    /**
     * @{@inheritdoc}
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
        $this->objectManager->remove($rule);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
