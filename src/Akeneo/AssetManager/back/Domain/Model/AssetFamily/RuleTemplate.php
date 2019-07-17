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

use Webmozart\Assert\Assert;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;

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
            return Condition::createFromNormalized($condition)->normalize();
        }, $content['conditions']);

        $actions = array_map(function (array $action) {
            return Action::createFromNormalized($action)->normalize();
        }, $content['actions']);

        return new self($conditions, $actions);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): CompiledRule
    {
        $compiledConditions = array_map(function (Condition $condition) use ($propertyAccessibleAsset) {
            return $condition->compile($propertyAccessibleAsset);
        }, $this->conditions);

        $compiledActions = array_map(function (Action $action) use ($propertyAccessibleAsset) {
            return $action->compile($propertyAccessibleAsset);
        }, $this->actions);

        return new CompiledRule($compiledConditions, $compiledActions);
    }
}
