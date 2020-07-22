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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

final class AttributeSpellcheck
{
    /** @var AttributeCode */
    private $attributeCode;

    /** @var \DateTimeImmutable */
    private $evaluatedAt;

    /** @var SpellcheckResultByLocaleCollection */
    private $result;

    public function __construct(AttributeCode $attributeCode, \DateTimeImmutable $evaluatedAt, SpellcheckResultByLocaleCollection $result)
    {
        $this->attributeCode = $attributeCode;
        $this->evaluatedAt = $evaluatedAt;
        $this->result = $result;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    public function getEvaluatedAt(): \DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getResult(): SpellcheckResultByLocaleCollection
    {
        return $this->result;
    }

    public function isToImprove(): ?bool
    {
        return $this->result->isToImprove();
    }
}
