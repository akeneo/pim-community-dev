<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Bundle\StorageUtilsBundle\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Association type remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeRemover implements RemoverInterface
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
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($associationType, array $options = [])
    {
        if (!$associationType instanceof AssociationTypeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface',
                    ClassUtils::getClass($associationType)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $associationTypeId = $associationType->getId();
        $this->eventDispatcher->dispatch(
            AssociationTypeEvents::PRE_REMOVE,
            new RemoveEvent($associationType, $associationTypeId)
        );

        $this->objectManager->remove($associationType);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(
            AssociationTypeEvents::POST_REMOVE,
            new RemoveEvent($associationType, $associationTypeId)
        );
    }
}
