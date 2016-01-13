<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\RuleEngineBundle\DependencyInjection\Compiler\AbstractOrderedPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register all action applier by priority
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RegisterRuleDenormalizerPass extends AbstractOrderedPass
{
    /** @staticvar */
    const RULE_DENORMALIZER_REGISTRY_DEF = 'pimee_catalog_rule.denormalizer.product_rule.chained';

    /** @staticvar */
    const RULE_DENORMALIZER_TAG = 'pimee_catalog_rule.denormalizer.product_rule';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::RULE_DENORMALIZER_REGISTRY_DEF)) {
            return;
        }

        $registry = $container->getDefinition(self::RULE_DENORMALIZER_REGISTRY_DEF);
        $ruleDenormalizers = $this->collectTaggedServices($container, self::RULE_DENORMALIZER_TAG);

        foreach ($ruleDenormalizers as $ruleDenormalizer) {
            $registry->addMethodCall('addDenormalizer', [$ruleDenormalizer]);
        }
    }
}
