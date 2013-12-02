<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @TODO Move this logic outside of Refactor in BAP-1998
 */
class TwigSandboxConfigurationPass implements CompilerPassInterface
{
    const EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY = 'oro_email.twig.email_security_policy';
    const EMAIL_TEMPLATE_RENDERER_SERVICE_KEY = 'oro_email.email_renderer';
    const DATE_FORMAT_EXTENSION_SERVICE_KEY = 'oro_locale.twig.date_time';
    const INTL_EXTENSION_SERVICE_KEY = 'twig.extension.intl';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY)
            && $container->hasDefinition(self::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY)
            && $container->hasDefinition(self::DATE_FORMAT_EXTENSION_SERVICE_KEY)
            && $container->hasDefinition(self::INTL_EXTENSION_SERVICE_KEY)
        ) {
            // register 'locale_date', 'locale_time' and 'locale_datetime' filters
            $securityPolicyDef = $container->getDefinition(self::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY);
            $filters = $securityPolicyDef->getArgument(1);
            $filters = array_merge($filters, array('oro_format_date', 'oro_format_time', 'oro_format_datetime'));
            $securityPolicyDef->replaceArgument(1, $filters);
            // register an twig extension implements these filters
            $rendererDef = $container->getDefinition(self::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY);
            $rendererDef->addMethodCall('addExtension', array(new Reference(self::DATE_FORMAT_EXTENSION_SERVICE_KEY)));
            // register Intl twig extension required for our date format extension
            $rendererDef->addMethodCall('addExtension', array(new Reference(self::INTL_EXTENSION_SERVICE_KEY)));
        }
    }
}
