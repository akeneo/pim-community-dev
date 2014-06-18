<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;

/**
 * Update the proposition with the current request data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SubmitPropositionFormSubscriber implements EventSubscriberInterface
{
    protected $formFactory;

    protected $request;

    public function __construct(
        FormFactoryInterface $formFactory,
        Request $request
    ) {
        $this->formFactory = $formFactory;
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return [
            PropositionEvents::PRE_UPDATE => 'submit',
        ];
    }

    public function submit(PropositionEvent $event)
    {
        $this
            ->formFactory
            ->create('pimee_workflow_proposition', $event->getProposition())
            ->submit($this->request);
    }
}
