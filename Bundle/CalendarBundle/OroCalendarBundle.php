<?php

namespace Oro\Bundle\CalendarBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\CalendarBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass;

class OroCalendarBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigSandboxConfigurationPass());
    }
}
