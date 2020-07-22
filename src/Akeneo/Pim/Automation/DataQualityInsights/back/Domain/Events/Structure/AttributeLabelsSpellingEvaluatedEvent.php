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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;

final class AttributeLabelsSpellingEvaluatedEvent
{
    /** @var AttributeSpellcheck */
    private $attributeSpellcheck;

    public function __construct(AttributeSpellcheck $attributeSpellcheck)
    {
        $this->attributeSpellcheck = $attributeSpellcheck;
    }

    public function getAttributeSpellcheck(): AttributeSpellcheck
    {
        return $this->attributeSpellcheck;
    }
}
