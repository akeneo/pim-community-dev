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

namespace Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Compile a RuleTemplate given an PropertyAccessibleAsset to create a RuleInterface
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleCompiler
{
    /** @var array We only allow those keys to be replaced by asset values in conditions */
    private const CONDITIONS_KEYS_TO_REPLACE = ['field', 'value'];

    /** @var array We only allow those keys to be replaced by asset values in actions */
    private const ACTIONS_KEYS_TO_REPLACE = ['field', 'value', 'items'];

    /**
     * Takes an $propertyAccessibleAsset to fill in the given $ruleTemplate. This results in a ready-to-use RuleInterface
     * for the RuleEngine of the PIM.
     *
     * Example of a rule template:
     * {
     *   conditions: [
     *      {
     *          field: "sku",
     *          operator: "EQUALS",
     *          value: "{{product_sku}}" # Will be replaced by the attribute "product_sku" of the asset
     *      }
     *   ],
     *   actions: [
     *      {
     *          type: "add",
     *          field: "{{attribute}}", # Will be replaced by the attribute "attribute" of the asset
     *          value: "{{code}}" # Will be replaced by the code of the asset
     *      }
     *   ]
     * }
     *
     * @throws \Exception
     */
    public function compile(RuleTemplate $ruleTemplate, PropertyAccessibleAsset $propertyAccessibleAsset): CompiledRule
    {
        $compiledConditions = $this->compileConditionsWithPropertyAccessibleAsset($ruleTemplate, $propertyAccessibleAsset);
        $compiledActions = $this->compileActionsWithPropertyAccessibleAsset($ruleTemplate, $propertyAccessibleAsset);

        return new CompiledRule($compiledConditions, $compiledActions);

//        $compiledContent = $this->compileTemplateWithPropertyAccessibleAsset($ruleTemplate, $propertyAccessibleAsset);
//
//        $ruleData = [
//            'code' => '',
//            'priority' => '',
//            'conditions' => $compiledContent['conditions'],
//            'actions' => $compiledContent['actions']
//        ];
//
//        return $this->ruleDenormalizer->denormalize($ruleData, Rule::class);
    }

    private function compileConditionsWithPropertyAccessibleAsset(
        RuleTemplate $ruleTemplate,
        PropertyAccessibleAsset $propertyAccessibleAsset
    ): array {
        $compiledConditions = [];

        foreach ($ruleTemplate->getConditions() as $condition) {
            foreach ($condition as $key => $value) {
                if (!in_array($key, self::CONDITIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                $condition[$key] = $this->replacePatterns($value, $propertyAccessibleAsset);
            }

            $compiledConditions[] = $condition;
        }

        return $compiledConditions;
    }

    private function compileActionsWithPropertyAccessibleAsset(
        RuleTemplate $ruleTemplate,
        PropertyAccessibleAsset $propertyAccessibleAsset
    ): array {
        $compiledActions = [];

        foreach ($ruleTemplate->getActions() as $action) {
            foreach ($action as $key => $value) {
                if (!in_array($key, self::ACTIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                if (is_array($action[$key])) {
                    foreach ($action[$key] as $i => $valueToReplace) {
                        $action[$key][$i] = $this->replacePatterns($valueToReplace, $propertyAccessibleAsset);
                    }
                } else {
                    $action[$key] = $this->replacePatterns($value, $propertyAccessibleAsset);
                }
            }

            $compiledActions[] = $action;
        }

        return $compiledActions;
    }

    private function replacePatterns(string $ruleValue, PropertyAccessibleAsset $propertyAccessibleAsset): string
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);

        foreach ($matchedPatterns[1] as $pattern) {
            if (!$propertyAccessibleAsset->hasValue(trim($pattern))) {
                continue;
            }

            $assetValue = $propertyAccessibleAsset->getValue(trim($pattern));
            if (is_array($assetValue)) {
                $assetValue = implode(',', $assetValue);
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
