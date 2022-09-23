<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue as ProductReferenceEntityValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

class ReferenceEntityValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_reference_entity_value_from_product_value(): void
    {
        $expectedValue = new ReferenceEntityValue('alessi');

        $productValue = ProductReferenceEntityValue::value(
            'reference_entity_attribute_code',
            RecordCode::fromString('alessi')
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'akeneo_reference_entity';
    }
}
