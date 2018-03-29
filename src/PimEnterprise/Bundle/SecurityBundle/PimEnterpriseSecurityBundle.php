<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise Security Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseSecurityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterSerializerPass('pimee_authorization_serializer'));
    }
}
