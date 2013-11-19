<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

use Oro\Bundle\InstallerBundle\InstallerEvents;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            return $this->redirect($this->generateUrl('oro_default'));
        }

        set_time_limit(120);

        $this->get('event_dispatcher')->dispatch(InstallerEvents::FINISH);

        $this->complete();

        return $this->render('OroInstallerBundle:Process/Step:final.html.twig');
    }
}
