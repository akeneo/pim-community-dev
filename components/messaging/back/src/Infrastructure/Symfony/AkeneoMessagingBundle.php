<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony;

use Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\DependencyInjection\AkeneoMessagingCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoMessagingBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AkeneoMessagingCompilerPass())
        ;
    }
}
