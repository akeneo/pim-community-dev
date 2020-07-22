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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;

final class AttributeOptionSpellcheck
{
    /** @var AttributeOptionCode */
    private $attributeOptionCode;

    /** @var \DateTimeImmutable */
    private $evaluatedAt;

    /** @var SpellcheckResultByLocaleCollection */
    private $result;

    public function __construct(
        AttributeOptionCode $attributeOptionCode,
        \DateTimeImmutable $evaluatedAt,
        SpellcheckResultByLocaleCollection $result
    ) {
        $this->attributeOptionCode = $attributeOptionCode;
        $this->evaluatedAt = $evaluatedAt;
        $this->result = $result;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeOptionCode->getAttributeCode();
    }

    public function getAttributeOptionCode(): AttributeOptionCode
    {
        return $this->attributeOptionCode;
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
