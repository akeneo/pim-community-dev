<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\DoctrineTargetDocumentResolver;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver\DoctrineTargetEntityResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Resolves doctrine ORM Target models
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass implements CompilerPassInterface
{
    /** @var array */
    protected $interfaces;

    public function __construct(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        (new DoctrineTargetDocumentResolver())->resolve($container, $this->interfaces);
        (new DoctrineTargetEntityResolver())->resolve($container, $this->interfaces);
    }
}
