<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class WelcomeStep extends ControllerStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render('OroInstallerBundle:Process/Step:welcome.html.twig');
    }
}
