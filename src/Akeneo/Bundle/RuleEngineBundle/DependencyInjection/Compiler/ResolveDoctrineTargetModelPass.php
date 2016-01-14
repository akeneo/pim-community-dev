<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        $definitionClass = 'akeneo_rule_engine.model.rule_definition.class';
        $relationClass   = 'akeneo_rule_engine.model.rule_relation.class';

        return [
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface' => $definitionClass,
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleRelationInterface'   => $relationClass,
        ];
    }
}
