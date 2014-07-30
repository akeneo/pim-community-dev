<?php

namespace PimEnterprise\Bundle\VersioningBundle;

use Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise versioning bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseVersioningBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new SerializerPass('pimee_versioning.serializer'));
    }
}
