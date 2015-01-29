<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPassSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass');
    }

    function it_resolvers_doctrine_target_model(ContainerBuilder $container)
    {
        $this->process($container);
    }
}
