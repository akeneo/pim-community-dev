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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheckCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;

interface GetAttributeOptionSpellcheckQueryInterface
{
    public function getByAttributeOptionCode(AttributeOptionCode $attributeOptionCode): ?AttributeOptionSpellcheck;

    /**
     * @param AttributeCode $attributeCode
     * @param string[] $optionCodes
     *
     * @return AttributeOptionSpellcheck[]
     */
    public function getByAttributeAndOptionCodes(AttributeCode $attributeCode, array $optionCodes): array;

    public function evaluatedSince(\DateTimeImmutable $evaluatedSince): \Iterator;

    /**
     * @param AttributeCode $attributeCode
     *
     * @return AttributeOptionSpellcheck[]
     */
    public function getByAttributeCodeWithSpellingMistakes(AttributeCode $attributeCode): array;

    public function getByAttributeCode(AttributeCode $attributeCode): AttributeOptionSpellcheckCollection;
}
