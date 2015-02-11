<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemovingOptionsResolver;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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

    /** @var BaseRemovingOptionsResolver */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager               $objectManager
     * @param BaseRemovingOptionsResolver $optionsResolver
     * @param EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        BaseRemovingOptionsResolver $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
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
        $this->eventDispatcher->dispatch(AssociationTypeEvents::PRE_REMOVE, new GenericEvent($associationType));

        $this->objectManager->remove($associationType);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
