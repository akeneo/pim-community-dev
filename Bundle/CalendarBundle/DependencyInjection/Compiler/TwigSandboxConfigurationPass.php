<?php

namespace Oro\Bundle\CalendarBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigSandboxConfigurationPass implements CompilerPassInterface
{
    const EMAIL_TEMPLATE_SANDBOX_SERVICE_KEY = 'oro_email.twig.email_security_policy';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::EMAIL_TEMPLATE_SANDBOX_SERVICE_KEY)) {
            $def = $container->getDefinition(self::EMAIL_TEMPLATE_SANDBOX_SERVICE_KEY);
            $functions = $def->getArgument(4);
            $functions = array_merge($functions, array('calendar_date_range'));
            $def->replaceArgument(4, $functions);
        }
    }
}
