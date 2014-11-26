<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\DependencyInjection;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ResolveDoctrineTargetModelsPass extends AbstractResolveDoctrineTargetModelsPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return array(
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface'
            => 'pimee_catalog_rule.entity.rule_linked_resource.class',
        );
    }
}
