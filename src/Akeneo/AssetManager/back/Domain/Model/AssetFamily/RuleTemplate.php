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
    private const CONDITIONS = 'conditions';
    private const ACTIONS = 'actions';
    public const PRODUCT_SELECTIONS = 'product_selections';
    public const ASSIGN_ASSETS_TO = 'assign_assets_to';

    /** @var Condition[] */
    private array $conditions;

    /** @var Action[] */
    private array $actions;

    private function __construct(array $conditions, array $actions)
    {
        Assert::notEmpty($conditions, 'A rule template should have at least have one condition');
        Assert::notEmpty($actions, 'A rule template should have at least have one action');
        Assert::allIsInstanceOf($conditions, Condition::class);
        Assert::allIsInstanceOf($actions, Action::class);

        $this->conditions = $conditions;
        $this->actions    = $actions;
    }

    public static function createFromNormalized(array $content): RuleTemplate
    {
        Assert::keyExists($content, self::CONDITIONS);
        Assert::keyExists($content, self::ACTIONS);

        $conditions = array_map(fn(array $condition) => Condition::createFromNormalized($condition), $content[self::CONDITIONS]);

        $actions = array_map(fn(array $action) => Action::createFromNormalized($action), $content[self::ACTIONS]);

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
        Assert::keyExists($content, self::PRODUCT_SELECTIONS);
        Assert::keyExists($content, self::ASSIGN_ASSETS_TO);

        $conditions = self::createConditions($content);
        $actions = self::createActions($content);

        return new self($conditions, $actions);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): CompiledRule
    {
        $compiledConditions = array_map(fn(Condition $condition) => $condition->compile($propertyAccessibleAsset)->normalize(), $this->conditions);

        $compiledActions = array_map(fn(Action $action) => $action->compile($propertyAccessibleAsset)->normalize(), $this->actions);

        return new CompiledRule($compiledConditions, $compiledActions);
    }

    public function normalize(): array
    {
        $normalizedConditions = array_map(fn(Condition $condition) => $condition->normalize(), $this->conditions);

        $normalizedActions = array_map(fn(Action $action) => $action->normalize(), $this->actions);

        return [
            self::CONDITIONS => $normalizedConditions,
            self::ACTIONS    => $normalizedActions,
        ];
    }

    private static function createConditions(array $content): array
    {
        return array_map(fn(array $condition) => Condition::createFromProductLinkRule($condition), $content[self::PRODUCT_SELECTIONS]);
    }

    private static function createActions(array $content): array
    {
        return array_map(fn(array $action) => Action::createFromProductLinkRule($action), $content[self::ASSIGN_ASSETS_TO]);
    }
}
