<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\GroupEvents;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Group remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RemovingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                    $objectManager
     * @param RemovingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($group, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\GroupInterface',
                    ClassUtils::getClass($group)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $groupId = $group->getId();
        $this->eventDispatcher->dispatch(GroupEvents::PRE_REMOVE, new RemoveEvent($group, $groupId));

        $this->objectManager->remove($group);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(GroupEvents::POST_REMOVE, new RemoveEvent($group, $groupId));
    }
}
