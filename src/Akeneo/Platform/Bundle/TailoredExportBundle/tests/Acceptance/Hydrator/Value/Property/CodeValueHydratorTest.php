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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value\Property;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Code\CodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CodeValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;

class CodeValueHydratorTest extends AbstractPropertyValueHydratorTest
{
    /**
     * @test
     */
    public function it_returns_value_properties_from_product(): void
    {
        $productModel = new ProductModel();
        $productModel->setCode('a_code');

        $valueHydrated = $this->getHydrator()->hydrate(new PropertySource(
            'uuid',
            'code',
            null,
            null,
            OperationCollection::create([]),
            new CodeSelection()
        ), $productModel);
        $this->assertEquals(new CodeValue('a_code'), $valueHydrated);
    }

    public function it_returns_null_value_when_value_is_empty(): void
    {
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate(new PropertySource(
            'uuid',
            'code',
            null,
            null,
            OperationCollection::create([]),
            new CodeSelection()
        ), new ProductModel()));
    }
}
