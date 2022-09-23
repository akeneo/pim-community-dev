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

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractAssociationTypeValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function assertHydratedValueEquals(
        SourceValueInterface $expectedValue,
        ConnectorProduct $product,
        bool $isQuantified
    ): void {
        $hydratedValue = $this->getHydrator()->hydrate($product, 'X_SELL', $isQuantified);

        $this->assertEquals($expectedValue, $hydratedValue);
    }

    private function getHydrator(): AssociationTypeValueHydrator
    {
        return static::$container->get('Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator');
    }
}
