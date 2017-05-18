<?php

namespace Pim\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to add rules to the content type negotiator.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentTypeNegotiatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_api.negotiator.content_type_negotiator')) {
            return;
        }

        $configuration = $container->getParameter('pim_api.configuration');
        $rules = $configuration['content_type_negotiator']['rules'];
        foreach ($rules as $rule) {
            $this->addRule($rule, $container);
        }
    }

    private function addRule(array $rule, ContainerBuilder $container)
    {
        $matcher = $this->createRequestMatcher(
            $container,
            $rule['path'],
            $rule['host'],
            $rule['methods']
        );

        $container->getDefinition('pim_api.negotiator.content_type_negotiator')
            ->addMethodCall('add', ['matcher' => $matcher, 'rule' => $rule]);
    }

    private function createRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = null)
    {
        $arguments = [$path, $host, $methods];
        $serialized = serialize($arguments);
        $id = 'pim_api.content_type_negotiator.request_matcher.'.md5($serialized).sha1($serialized);

        if (!$container->hasDefinition($id)) {
            $container
                ->setDefinition($id, new DefinitionDecorator('fos_rest.format_request_matcher'))
                ->setArguments($arguments);
        }

        return new Reference($id);
    }
}
