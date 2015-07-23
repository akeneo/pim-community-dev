<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\FamilyEvents;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Family remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRemover implements RemoverInterface
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
    public function remove($family, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\FamilyInterface',
                    ClassUtils::getClass($family)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $familyId = $family->getId();
        $this->eventDispatcher->dispatch(FamilyEvents::PRE_REMOVE, new RemoveEvent($family, $familyId));

        $this->objectManager->remove($family);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(FamilyEvents::POST_REMOVE, new RemoveEvent($family, $familyId));
    }
}
