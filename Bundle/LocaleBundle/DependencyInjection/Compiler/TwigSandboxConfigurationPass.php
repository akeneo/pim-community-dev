<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection\Compiler;

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
            $filters = $def->getArgument(1);
            $filters = array_merge($filters, array('locale_date', 'locale_time', 'locale_datetime'));
            $def->replaceArgument(1, $filters);
        }
    }
}
