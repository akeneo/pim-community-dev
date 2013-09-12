<?php

namespace Oro\Bundle\InstallerBundle\Process;

use Symfony\Component\DependencyInjection\ContainerAware;

use Sylius\Bundle\FlowBundle\Process\Builder\ProcessBuilderInterface;
use Sylius\Bundle\FlowBundle\Process\Scenario\ProcessScenarioInterface;

class InstallerScenario extends ContainerAware implements ProcessScenarioInterface
{
    public function build(ProcessBuilderInterface $builder)
    {
        $builder
            ->add('welcome', new Step\WelcomeStep())
            ->add('check', new Step\CheckStep())
            ->add('configure', new Step\ConfigureStep())
            ->add('setup', new Step\SetupStep())
            ->setRedirect('oro_default');
    }
}
