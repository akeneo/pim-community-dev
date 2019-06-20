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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleCompiler
{
    private const KEYS_TO_REPLACE = ['field', 'value'];

    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    /** @var string */
    private $ruleClass;

    public function __construct(DenormalizerInterface $ruleDenormalizer, string $ruleClass)
    {
        $this->ruleDenormalizer = $ruleDenormalizer;
        $this->ruleClass = $ruleClass;
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
        $content = $this->fillTemplateWithAccessibleRecord($ruleTemplate, $accessibleRecord);

        $ruleData = [
            'code' => '',
            'priority' => '',
            'conditions' => $content['conditions'],
            'actions' => $content['actions']
        ];

        return $this->ruleDenormalizer->denormalize($ruleData, $this->ruleClass);
    }

    private function fillTemplateWithAccessibleRecord(RuleTemplate $ruleTemplate, AccessibleRecord $accessibleRecord): array
    {
        $compiledConditions = [];
        $compiledActions = [];

        foreach ($ruleTemplate->getConditions() as $condition) {
            foreach ($condition as $key => $value) {
                if (!in_array($key, self::KEYS_TO_REPLACE)) {
                    continue;
                }

                $condition[$key] = $this->replacePatterns($value, $accessibleRecord);
            }

            $compiledConditions[] = $condition;
        }

        foreach ($ruleTemplate->getActions() as $action) {
            foreach ($action as $key => $value) {
                if (!in_array($key, self::KEYS_TO_REPLACE)) {
                    continue;
                }

                $action[$key] = $this->replacePatterns($value, $accessibleRecord);
            }

            $compiledActions[] = $action;
        }

        return [
            'conditions' => $compiledConditions,
            'actions' => $compiledActions,
        ];
    }

    private function replacePatterns(string $ruleValue, AccessibleRecord $accessibleRecord): string
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);

        foreach ($matchedPatterns[1] as $pattern) {
            if (!$accessibleRecord->hasValue($pattern)) {
                continue;
            }

            $assetValue = $accessibleRecord->getValue($pattern);
            if (is_array($assetValue)) {
                $assetValue = implode(',', $assetValue);
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
