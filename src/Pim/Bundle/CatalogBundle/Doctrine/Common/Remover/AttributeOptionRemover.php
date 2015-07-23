<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute option remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRemover implements RemoverInterface
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
    public function remove($attributeOption, array $options = [])
    {
        if (!$attributeOption instanceof AttributeOptionInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface',
                    ClassUtils::getClass($attributeOption)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $attributeOptionId = $attributeOption->getId();
        $this->eventDispatcher->dispatch(
            AttributeOptionEvents::PRE_REMOVE,
            new RemoveEvent($attributeOption, $attributeOptionId)
        );

        $this->objectManager->remove($attributeOption);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(
            AttributeOptionEvents::POST_REMOVE,
            new RemoveEvent($attributeOption, $attributeOptionId)
        );
    }
}
