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

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplateCollection implements \IteratorAggregate
{
    public const EMPTY = [];

    /** @var RuleTemplate[] */
    private array $ruleTemplates;

    private function __construct(array $ruleTemplates)
    {
        Assert::allIsInstanceOf($ruleTemplates, RuleTemplate::class);

        $this->ruleTemplates = $ruleTemplates;
    }

    public static function createFromNormalized(array $normalizedRuleTemplates): self
    {
        Assert::allIsArray($normalizedRuleTemplates);

        $ruleTemplates = [];
        foreach ($normalizedRuleTemplates as $ruleTemplate) {
            $ruleTemplates[] = RuleTemplate::createFromNormalized($ruleTemplate);
        }

        return new self($ruleTemplates);
    }

    public static function createFromProductLinkRules(array $productLinkRules): self
    {
        Assert::allIsArray($productLinkRules);

        $ruleTemplates = [];
        foreach ($productLinkRules as $productLinkRule) {
            $ruleTemplates[] = RuleTemplate::createFromProductLinkRule($productLinkRule);
        }

        return new self($ruleTemplates);
    }

    public static function empty(): self
    {
        return new self(self::EMPTY);
    }

    public function isEmpty(): bool
    {
        return self::EMPTY === $this->ruleTemplates;
    }

    public function normalize(): array
    {
        $normalizedRuleTemplates = [];
        /** @var RuleTemplate $ruleTemplate */
        foreach ($this->ruleTemplates as $ruleTemplate) {
            $normalizedRuleTemplates[] = $ruleTemplate->normalize();
        }

        return $normalizedRuleTemplates;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->ruleTemplates);
    }
}
