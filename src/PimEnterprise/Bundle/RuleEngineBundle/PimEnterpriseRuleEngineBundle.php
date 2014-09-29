<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle;

use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterLoaderPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterRunnerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PIM Enterprise Rule Engine Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseRuleEngineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterRunnerPass())
            ->addCompilerPass(new RegisterLoaderPass())
        ;
    }
}
