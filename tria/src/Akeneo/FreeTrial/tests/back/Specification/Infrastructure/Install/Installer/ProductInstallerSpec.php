<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixturesReader,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver,
        ValidatorInterface $productValidator
    ) {
        $this->beConstructedWith($fixturesReader, $productBuilder, $updater, $saver, $productValidator, 2);
    }

    public function it_successfully_installs_products(
        $fixturesReader,
        $productBuilder,
        $updater,
        $saver,
        $productValidator,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $productData1 = [
            'identifier' => 'PLG395577',
            'family' => 'clothing',
            'values' => [
                'name' => [[
                    'locale' => 'en_US', 'scope' => null, 'data' => 'Foo'
                ]]
            ]
        ];
        $productData2 = [
            'identifier' => 'PLG513725',
            'family' => 'food',
            'values' => [
                'name' => [[
                    'locale' => 'en_US', 'scope' => null, 'data' => 'Bar'
                ]]
            ]
        ];
        $productData3 = [
            'identifier' => 'PLG01PKEU',
            'family' => 'appliances',
            'values' => [
                'name' => [[
                    'locale' => 'en_US', 'scope' => null, 'data' => 'Ziggy'
                ]]
            ]
        ];

        $fixturesReader->read()->willReturn(new \ArrayIterator([$productData1, $productData2, $productData3]));

        $productBuilder->createProduct()->willReturn($product1, $product2, $product3);

        $productData1['values']['sku'] = [['locale' => null, 'scope' => null, 'data' => 'PLG395577']];
        $productData2['values']['sku'] = [['locale' => null, 'scope' => null, 'data' => 'PLG513725']];
        $productData3['values']['sku'] = [['locale' => null, 'scope' => null, 'data' => 'PLG01PKEU']];

        $updater->update($product1, $productData1)->shouldBeCalled();
        $updater->update($product2, $productData2)->shouldBeCalled();
        $updater->update($product3, $productData3)->shouldBeCalled();

        $productValidator->validate(Argument::cetera())->willReturn(new ConstraintViolationList([]));

        $saver->saveAll([$product1, $product2])->shouldBeCalled();
        $saver->saveAll([$product3])->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_product_is_invalid(
        $fixturesReader,
        $productBuilder,
        $updater,
        $saver,
        $productValidator,
        ProductInterface $product1,
        ConstraintViolationInterface $violation
    ) {
        $productData1 = [
            'identifier' => 'invalid',
            'values' => []
        ];
        $productData2 = [
            'identifier' => 'PLG513725',
            'family' => 'food',
            'values' => [
                'name' => [[
                    'locale' => 'en_US', 'scope' => null, 'data' => 'Bar'
                ]]
            ]
        ];

        $fixturesReader->read()->willReturn(new \ArrayIterator([$productData1, $productData2]));
        $productBuilder->createProduct()->willReturn($product1);
        $productData1['values']['sku'] = [['locale' => null, 'scope' => null, 'data' => 'invalid']];
        $updater->update($product1, $productData1)->shouldBeCalled();

        $productValidator
            ->validate($product1, Argument::cetera())
            ->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\Exception::class)->during('install');
    }
}
