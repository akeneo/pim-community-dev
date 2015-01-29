<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoctrineTargetDocumentResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\DoctrineTargetDocumentResolver');
    }

    function it_is_resolver()
    {
        $this->shouldHaveType(
            'Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\AbstractDoctrineTargetResolver'
        );
    }
}
