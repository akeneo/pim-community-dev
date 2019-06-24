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

namespace Akeneo\AssetManager\Infrastructure\Rule;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Asset\AccessibleAsset;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Compile a RuleTemplate given an AccessibleAsset to create a RuleInterface
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleCompiler
{
    /** @var array We only allow those keys to be replaced by asset values in conditions */
    private const CONDITIONS_KEYS_TO_REPLACE = ['field', 'value'];

    /** @var array We only allow those keys to be replaced by asset values in actions */
    private const ACTIONS_KEYS_TO_REPLACE = ['field', 'value'];

    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    public function __construct(DenormalizerInterface $ruleDenormalizer)
    {
        $this->ruleDenormalizer = $ruleDenormalizer;
    }

    /**
     * Takes an $accessibleAsset to fill in the given $ruleTemplate. This results in a ready-to-use RuleInterface
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
    public function compile(RuleTemplate $ruleTemplate, AccessibleAsset $accessibleAsset): RuleInterface
    {
        $compiledContent = $this->compileTemplateWithAccessibleAsset($ruleTemplate, $accessibleAsset);

        $ruleData = [
            'code' => '',
            'priority' => '',
            'conditions' => $compiledContent['conditions'],
            'actions' => $compiledContent['actions']
        ];

        return $this->ruleDenormalizer->denormalize($ruleData, Rule::class);
    }

    private function compileTemplateWithAccessibleAsset(RuleTemplate $ruleTemplate, AccessibleAsset $accessibleAsset): array
    {
        $compiledConditions = $this->compileConditionsWithAccessibleAsset($ruleTemplate, $accessibleAsset);
        $compiledActions = $this->compileActionsWithAccessibleAsset($ruleTemplate, $accessibleAsset);

        return [
            'conditions' => $compiledConditions,
            'actions' => $compiledActions,
        ];
    }

    private function compileConditionsWithAccessibleAsset(RuleTemplate $ruleTemplate, AccessibleAsset $accessibleAsset): array
    {
        $compiledConditions = [];

        foreach ($ruleTemplate->getConditions() as $condition) {
            foreach ($condition as $key => $value) {
                if (!in_array($key, self::CONDITIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                $condition[$key] = $this->replacePatterns($value, $accessibleAsset);
            }

            $compiledConditions[] = $condition;
        }

        return $compiledConditions;
    }

    private function compileActionsWithAccessibleAsset(RuleTemplate $ruleTemplate, AccessibleAsset $accessibleAsset): array
    {
        $compiledActions = [];

        foreach ($ruleTemplate->getActions() as $action) {
            foreach ($action as $key => $value) {
                if (!in_array($key, self::ACTIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                $action[$key] = $this->replacePatterns($value, $accessibleAsset);
            }

            $compiledActions[] = $action;
        }

        return $compiledActions;
    }

    private function replacePatterns(string $ruleValue, AccessibleAsset $accessibleAsset): string
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);

        foreach ($matchedPatterns[1] as $pattern) {
            if (!$accessibleAsset->hasValue(trim($pattern))) {
                continue;
            }

            $assetValue = $accessibleAsset->getValue(trim($pattern));
            if (is_array($assetValue)) {
                $assetValue = implode(',', $assetValue);
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
