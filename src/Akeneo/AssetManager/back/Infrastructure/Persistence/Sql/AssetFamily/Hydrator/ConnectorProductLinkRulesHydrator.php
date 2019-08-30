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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;

/**
 * Hydrate "Rule Templates" coming from the DB to public "Product Link Rules" format.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ConnectorProductLinkRulesHydrator
{
    private const CONDITIONS = 'conditions';
    private const ACTIONS = 'actions';
    private const PRODUCT_SELECTIONS = 'product_selections';
    private const ASSIGN_ASSETS_TO = 'assign_assets_to';

    public function hydrate(array $ruleTemplates): array
    {
        $productLinkRules = [];
        foreach ($ruleTemplates as $ruleTemplate) {
            $productSelections = $this->transformConditionsToProductSelections($ruleTemplate[self::CONDITIONS]);
            $assignments = $this->transformActionsToAssignments($ruleTemplate[self::ACTIONS]);

            $productLinkRules[] = [
                self::PRODUCT_SELECTIONS => $productSelections,
                self::ASSIGN_ASSETS_TO => $assignments,
            ];
        }

        return $productLinkRules;
    }

    private function transformConditionsToProductSelections(array $conditions): array
    {
        return $conditions;
    }

    private function transformActionsToAssignments(array $actions): array
    {
        $assignments = [];
        foreach ($actions as $action) {
            $assignments[] = [
                'attribute' => $action['field'],
                'locale' => $action['locale'],
                'channel' => $action['channel'],
                'mode' => Action::ADD_MODE === $action['type'] ? Action::ADD_MODE : Action::REPLACE_MODE,
            ];
        }

        return $assignments;
    }
}
