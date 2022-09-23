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

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AttributeValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractAttributeValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function assertHydratedValueEquals(SourceValueInterface $expectedValue, ?ValueInterface $value): void
    {
        $product = new Product();
        $product->setIdentifier('product_identifier');
        $hydratedValue = $this->getHydrator()->hydrate($value, $this->getAttributeType(), $product);

        $this->assertEquals($expectedValue, $hydratedValue);
    }

    abstract protected function getAttributeType(): string;

    private function getHydrator(): AttributeValueHydrator
    {
        return static::$container->get('Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AttributeValueHydrator');
    }
}
