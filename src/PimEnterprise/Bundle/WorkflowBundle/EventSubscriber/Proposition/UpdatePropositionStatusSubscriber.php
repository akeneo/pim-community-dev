<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;

/**
 * Update the proposition with the current request data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class UpdatePropositionStatusSubscriber implements EventSubscriberInterface
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
            PropositionEvents::PRE_UPDATE => 'update',
        ];
    }

    /**
     * Update a proposition status by submitting the current request
     * against a proposition form
     *
     * @param PropositionEvent $event
     */
    public function update(PropositionEvent $event)
    {
        $this
            ->formFactory
            ->create('pimee_workflow_proposition', $event->getProposition())
            ->submit($this->container->get('request'));
    }
}
