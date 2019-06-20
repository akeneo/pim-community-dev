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

namespace Akeneo\ReferenceEntity\Infrastructure\Rule;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\RuleTemplate;
use Akeneo\ReferenceEntity\Domain\Query\Record\AccessibleRecord;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleCompiler
{
    private const CONDITIONS_KEYS_TO_REPLACE = ['field', 'value'];
    private const ACTIONS_KEYS_TO_REPLACE = ['field', 'value'];

    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    public function __construct(DenormalizerInterface $ruleDenormalizer)
    {
        $this->ruleDenormalizer = $ruleDenormalizer;
    }

    /**
     * {
     *   conditions: [
     *      {
     *          field: "sku",
     *          operator: "EQUALS",
     *          value: "{{product_sku}}"
     *      }
     *   ],
     *   actions: [
     *      {
     *          type: "add",
     *          field: "{{attribute}}",
     *          value: "{{code}}"
     *      }
     *   ]
     * }
     *
     * @throws \Exception
     */
    public function compile(RuleTemplate $ruleTemplate, AccessibleRecord $accessibleRecord): RuleInterface
    {
        $compiledContent = $this->compileTemplateWithAccessibleRecord($ruleTemplate, $accessibleRecord);

        $ruleData = [
            'code' => '',
            'priority' => '',
            'conditions' => $compiledContent['conditions'],
            'actions' => $compiledContent['actions']
        ];

        return $this->ruleDenormalizer->denormalize($ruleData, Rule::class);
    }

    private function compileTemplateWithAccessibleRecord(RuleTemplate $ruleTemplate, AccessibleRecord $accessibleRecord): array
    {
        $compiledConditions = $this->compileConditionsWithAccessibleRecord($ruleTemplate, $accessibleRecord);
        $compiledActions = $this->compileActionsWithAccessibleRecord($ruleTemplate, $accessibleRecord);

        return [
            'conditions' => $compiledConditions,
            'actions' => $compiledActions,
        ];
    }

    private function compileConditionsWithAccessibleRecord(RuleTemplate $ruleTemplate, AccessibleRecord $accessibleRecord): array
    {
        $compiledConditions = [];

        foreach ($ruleTemplate->getConditions() as $condition) {
            foreach ($condition as $key => $value) {
                if (!in_array($key, self::CONDITIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                $condition[$key] = $this->replacePatterns($value, $accessibleRecord);
            }

            $compiledConditions[] = $condition;
        }

        return $compiledConditions;
    }

    private function compileActionsWithAccessibleRecord(RuleTemplate $ruleTemplate, AccessibleRecord $accessibleRecord): array
    {
        $compiledActions = [];

        foreach ($ruleTemplate->getActions() as $action) {
            foreach ($action as $key => $value) {
                if (!in_array($key, self::ACTIONS_KEYS_TO_REPLACE)) {
                    continue;
                }

                $action[$key] = $this->replacePatterns($value, $accessibleRecord);
            }

            $compiledActions[] = $action;
        }

        return $compiledActions;
    }

    private function replacePatterns(string $ruleValue, AccessibleRecord $accessibleRecord): string
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);

        foreach ($matchedPatterns[1] as $pattern) {
            if (!$accessibleRecord->hasValue(trim($pattern))) {
                continue;
            }

            $assetValue = $accessibleRecord->getValue(trim($pattern));
            if (is_array($assetValue)) {
                $assetValue = implode(',', $assetValue);
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
