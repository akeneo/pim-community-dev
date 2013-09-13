<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class ConfigureStep extends ControllerStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'OroInstallerBundle:Process/Step:configure.html.twig',
            array(
                'form' => $this->createConfigurationForm()->createView()
            )
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $form = $this->createConfigurationForm();

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $this->get('oro_installer.yaml_persister')->dump($form->getData());

            return $this->complete();
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:configure.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    protected function createConfigurationForm()
    {
        return $this->createForm(
            'oro_installer_configuration',
            $this->get('oro_installer.yaml_persister')->parse()
        );
    }
}
