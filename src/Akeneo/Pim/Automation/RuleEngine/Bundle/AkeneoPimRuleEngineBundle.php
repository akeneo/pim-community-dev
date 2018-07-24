<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle;

use Akeneo\Pim\Automation\RuleEngine\Bundle\DependencyInjection\Compiler\RegisterRuleDenormalizerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PIM Enterprise Product Rule Bundle
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class AkeneoPimRuleEngineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterRuleDenormalizerPass());
    }
}
