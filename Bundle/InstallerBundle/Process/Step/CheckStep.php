<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class CheckStep extends ControllerStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            return $this->redirect($this->generateUrl('oro_default'));
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:check.html.twig',
            array(
                'collections' => $this->get('oro_installer.requirements'),
            )
        );
    }
}
