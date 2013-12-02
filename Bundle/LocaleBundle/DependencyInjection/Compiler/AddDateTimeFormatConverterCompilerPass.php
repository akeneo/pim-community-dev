<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class AddDateTimeFormatConverterCompilerPass implements CompilerPassInterface
{
    const CONVERTER_TAG = 'oro_locale.format_converter.date_time';
    const CONVERTER_REGISTRY_SERVICE = 'oro_locale.format_converter.date_time.registry';

    /**
     * @param ContainerBuilder $container
     * @throws LogicException
     */
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->getDefinition(self::CONVERTER_REGISTRY_SERVICE);

        foreach ($container->findTaggedServiceIds(self::CONVERTER_TAG) as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                if (empty($eachTag['alias'])) {
                    throw new LogicException(
                        sprintf('Tag %s for service %s must have an alias', self::CONVERTER_TAG, $id)
                    );
                }

                $registryDefinition->addMethodCall(
                    'addFormatConverter',
                    array($eachTag['alias'], $container->getDefinition($id))
                );
            }
        }
    }
}
