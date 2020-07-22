<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure;

final class AttributeOptionSpellcheckCollection
{
    /** @var AttributeOptionSpellcheck[] */
    private $attributeOptionSpellchecks = [];

    public function add(AttributeOptionSpellcheck $attributeOptionSpellcheck): self
    {
        $this->attributeOptionSpellchecks[] = $attributeOptionSpellcheck;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->attributeOptionSpellchecks);
    }

    public function hasAttributeOptionToImprove(): bool
    {
        foreach ($this->attributeOptionSpellchecks as $attributeOptionSpellcheck) {
            if ($attributeOptionSpellcheck->isToImprove()) {
                return true;
            }
        }

        return false;
    }

    public function hasOnlyGoodSpellchecks(): bool
    {
        foreach ($this->attributeOptionSpellchecks as $attributeOptionSpellcheck) {
            if (false !== $attributeOptionSpellcheck->isToImprove()) {
                return false;
            }
        }

        return $this->isEmpty() ? false : true;
    }

    public function toArray(): array
    {
        return $this->attributeOptionSpellchecks;
    }
}
