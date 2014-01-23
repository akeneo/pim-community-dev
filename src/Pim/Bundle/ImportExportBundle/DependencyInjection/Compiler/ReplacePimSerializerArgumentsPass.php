<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * Compiler pass to register tagged encoders and normalizers into the pim serializer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplacePimSerializerArgumentsPass implements CompilerPassInterface
{
    protected $factory;

    /**
     * @param ReferenceFactory|null $factory
     */
    public function __construct(ReferenceFactory $factory = null)
    {
        $this->factory = $factory ?: new ReferenceFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_serializer')) {
            return;
        }

        $normalizerRefs = [];
        $encoderRefs    = [];

        foreach ($container->findTaggedServiceIds('pim_serializer.normalizer') as $id => $attributes) {
            $normalizerRefs[] = $this->factory->createReference($id);
        }

        foreach ($container->findTaggedServiceIds('pim_serializer.encoder') as $id => $attributes) {
            $encoderRefs[] = $this->factory->createReference($id);
        }

        $serializerDef = $container->getDefinition('pim_serializer');
        $serializerDef->setArguments([$normalizerRefs, $encoderRefs]);
    }
}
