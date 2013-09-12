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
            array('form' => $this->createConfigurationForm()->createView())
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $form = $this->createConfigurationForm();

        if ($this->getRequest()->isMethod('POST') && $form->bind($this->getRequest())->isValid()) {
            $data = $form->getData();

            $this->get('oro_installer.yaml_persister')->dump($data);

            return $this->complete();
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:configure.html.twig',
            array('form' => $form->createView())
        );
    }

    protected function createConfigurationForm()
    {
        return $this->createForm(
            'oro_configuration',
            $this->get('oro_installer.yaml_persister')->parse()
        );
    }
}
