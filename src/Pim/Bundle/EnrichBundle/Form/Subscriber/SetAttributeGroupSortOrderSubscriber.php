<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * A subscriber that sets the attribute group sort order
 * when creating a new attribute group
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeGroupSortOrderSubscriber implements EventSubscriberInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $repository;

    /**
     * @param AttributeGroupRepositoryInterface $repository
     */
    public function __construct(AttributeGroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || null !== $data->getId()) {
            return;
        }

        $data->setSortOrder($this->repository->getMaxSortOrder() + 1);
    }
}
