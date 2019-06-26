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

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplateCollection
{
    private $ruleTemplates;

    private function __construct(array $ruleTemplates)
    {
        foreach ($ruleTemplates as $ruleTemplate) {
            if (!is_object($ruleTemplate)) {
                throw new \InvalidArgumentException(sprintf('Expecting rule template to be an object, %s given.', gettype($ruleTemplate)));
            }

            if (!($ruleTemplate instanceof RuleTemplate)) {
                throw new \InvalidArgumentException(sprintf('Expecting rule template to be an instance of RuleTemplate, %s given.', get_class($ruleTemplate)));
            }
        }

        $this->ruleTemplates = $ruleTemplates;
    }

    public static function fromArray(array $ruleTemplates): self
    {
        return new self($ruleTemplates);
    }

    public function normalize(): array
    {
        $normalizedRuleTemplates = [];
        /** @var RuleTemplate $ruleTemplate */
        foreach ($this->ruleTemplates as $ruleTemplate) {
            $normalizedRuleTemplates[] = $ruleTemplate->getContent();
        }

        return $normalizedRuleTemplates;
    }
}
