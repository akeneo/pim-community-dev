<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Update the product draft with the current request data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class UpdateProductDraftStatusSubscriber implements EventSubscriberInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ContainerInterface   $container
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ContainerInterface $container
    ) {
        $this->formFactory = $formFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::PRE_UPDATE => 'update',
        ];
    }

    /**
     * Update a product draft status by submitting the current request
     * against a product draft form
     *
     * @param ProductDraftEvent $event
     */
    public function update(ProductDraftEvent $event)
    {
        $this
            ->formFactory
            ->create('pimee_workflow_product_draft', $event->getProductDraft())
            ->submit($this->container->get('request'));
    }
}
