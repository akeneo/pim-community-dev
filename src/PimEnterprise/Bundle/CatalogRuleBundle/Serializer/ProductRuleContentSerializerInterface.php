<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Serialize and deserialize a product rule content.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface ProductRuleContentSerializerInterface
{
    /**
     * @param RuleInterface $rule
     *
     * @return string the rule content encoded
     */
    public function serialize(RuleInterface $rule);

    /**
     * @param string $content
     *
     * @return array that contains the key "actions" and "conditions" with
     *               "conditions" array of PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface
     *               "actions"    array of PimEnterprise\Bundle\RuleEngineBundle\Model\ActionInterface
     *               (either PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction or
     *               PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction)
     *
     * @throws \LogicException in case the content is invalid
     */
    public function deserialize($content);
}
