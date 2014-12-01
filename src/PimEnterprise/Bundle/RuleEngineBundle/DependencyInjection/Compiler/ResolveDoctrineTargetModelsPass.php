<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ResolveDoctrineTargetModelsPass extends AbstractResolveDoctrineTargetModelsPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface'
                => 'pimee_rule_engine.entity.rule_definition.class',
        ];
    }
}
