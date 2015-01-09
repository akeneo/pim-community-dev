<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\AbstractDoctrineTargetResolver;
use Behat\Behat\Definition\Annotation\Definition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractDoctrineTargetResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf(
            'spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\DoctrineTargetResolver'
        );
    }

    function it_resolvers_targets(ContainerBuilder $container,  Definition $resolverDefinition)
    {
        $container->hasDefinition('doctrine_mongodb.odm.listeners.resolve_target_document')->willReturn(true);
        $container->findDefinition('doctrine_mongodb.odm.listeners.resolve_target_document')
            ->willReturn($resolverDefinition);

        $container->hasParameter('pim.model.catalog')->willReturn(true);
        $container->getParameter('pim.model.catalog')
            ->willReturn('spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\MyModel');

        $resolverDefinition->addMethodCall(
            'addResolveTargetDocument',
            [
                'spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\MyModelInterface',
                'spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\MyModel',
                [],
            ]
        )->shouldBeCalled();

        $this->resolver($container, [
            'pim.model.catalog' => 'spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\MyModelInterface'
        ]);
    }
}

class DoctrineTargetResolver extends AbstractDoctrineTargetResolver
{
    protected function getResolverDefinitionKey()
    {
        return 'doctrine_mongodb.odm.listeners.resolve_target_document';
    }

    protected function getResolverMethod()
    {
        return 'addResolveTargetDocument';
    }
}

interface MyModelInterface
{
}

class MyModel
{
}