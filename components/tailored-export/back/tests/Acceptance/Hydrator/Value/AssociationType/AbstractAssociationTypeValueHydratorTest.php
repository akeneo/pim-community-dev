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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractAssociationTypeValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function assertHydratedValueEquals(
        SourceValueInterface $expectedValue,
        ProductInterface $product,
        bool $isQuantified
    ): void {
        $hydratedValue = $this->getHydrator()->hydrate($product, 'X_SELL', $isQuantified);

        $this->assertEquals($expectedValue, $hydratedValue);
    }

    private function getHydrator(): AssociationTypeValueHydrator
    {
        return static::getContainer()->get('Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator');
    }
}
