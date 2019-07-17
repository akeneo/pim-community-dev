<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Webmozart\Assert\Assert;

/**
 * A RuleTemplate is the skeleton of a RuleInterface.
 * It allows patterns that will be filled by values of an asset.
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplate
{
    /** @var Condition[] */
    private $conditions;

    /** @var Action[] */
    private $actions;

    private function __construct(array $conditions, array $actions)
    {
        $this->conditions = $conditions;
        $this->actions    = $actions;
    }

    public static function createFromNormalized(array $content): RuleTemplate
    {
        Assert::keyExists($content, 'conditions');
        Assert::keyExists($content, 'actions');

        $conditions = array_map(function (array $condition) {
            return Condition::createFromNormalized($condition);
        }, $content['conditions']);

        $actions = array_map(function (array $action) {
            return Action::createFromNormalized($action);
        }, $content['actions']);

        return new self($conditions, $actions);
    }


    /**
     * “product_link_rule”:{
        “product_selections”:[{
            “field”: “sku”,
            “operator”: “EQUALS”,
            “value”: “product_ref”,
            “channel”: “ecommerce”, (optional)
            “locale”: “fr_FR” (optional)
        }],
        “assign_assets_to”:[{
            “attribute”:”my_product_attribute”,
            “channel”:”ecommerce”, (optional)
            “locale”:”fr_FR”, (optional)
            “mode”:”add” or “replace”
        }]
    }
     */
    public static function createFromProductLinkRule(array $content): RuleTemplate
    {
        Assert::keyExists($content, 'product_selections');
        Assert::keyExists($content, 'assign_assets_to');

        $conditions = array_map(function (array $condition) {
            return Condition::createFromProductLinkRule($condition);
        }, $content['product_selections']);

        $actions = array_map(function (array $action) {
            return Action::createFromProductLinkRule($action);
        }, $content['assign_assets_to']);

        return new self($conditions, $actions);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): CompiledRule
    {
        $compiledConditions = array_map(function (Condition $condition) use ($propertyAccessibleAsset) {
            return $condition->compile($propertyAccessibleAsset)->normalize();
        }, $this->conditions);

        $compiledActions = array_map(function (Action $action) use ($propertyAccessibleAsset) {
            return $action->compile($propertyAccessibleAsset)->normalize();
        }, $this->actions);

        return new CompiledRule($compiledConditions, $compiledActions);
    }

    public function normalize(): array
    {
        $normalizedConditions = array_map(function (Condition $condition) {
            /** @var Condition $condition */
            return $condition->normalize();
        }, $this->conditions);

        $normalizedActions = array_map(function (Action $action) {
            /** @var Action $action */
            return $action->normalize();
        }, $this->actions);

        return [
            'conditions' => $normalizedConditions,
            'actions' => $normalizedActions,
        ];
    }
}
