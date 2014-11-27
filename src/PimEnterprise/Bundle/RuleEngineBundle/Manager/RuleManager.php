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

        $this->eventDispatcher->dispatch(RuleEvents::PRE_REMOVE, new RuleEvent($rule));

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->remove($rule);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_REMOVE, new RuleEvent($rule));
    }

    /**
     * @{@inheritdoc}
     */
    public function save($rule, array $options = [])
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE, new RuleEvent($rule));

        //todo: get object we want to apply the rule
        //todo: save it into the table with the link between the resource and the object

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE, new RuleEvent($rule));
    }
}
