<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\FileTransformerBundle;

use Akeneo\Bundle\FileTransformerBundle\DependencyInjection\Compiler\RegisterTransformationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register the bundle
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AkeneoFileTransformerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterTransformationPass());
    }
}
