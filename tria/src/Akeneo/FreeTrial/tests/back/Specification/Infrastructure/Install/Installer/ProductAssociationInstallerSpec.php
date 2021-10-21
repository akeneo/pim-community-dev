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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class ProductAssociationInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixtureReader,
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith($fixtureReader, $productRepository, $updater, $saver);
    }

    public function it_successfully_installs_product_associations(
        $fixtureReader,
        $productRepository,
        $updater,
        $saver,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $productAssociation1 = [
            'identifier' => 'PLG566522',
            'associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'SUBSTITUTION' => [
                    'products' => ['PLG566523', 'PLG570428'],
                    'product_models' => [],
                    'groups' => []
                ]
            ]
        ];

        $productAssociation2 = [
            'identifier' => '11332479',
            'associations' => [
                'SUBSTITUTION' => [
                    'products' => ['11332477'],
                    'product_models' => [],
                    'groups' => []
                ]
            ]
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$productAssociation1, $productAssociation2]));

        $productRepository->findOneByIdentifier('PLG566522')->willReturn($product1);
        $productRepository->findOneByIdentifier('11332479')->willReturn($product2);

        $updater->update(
            $product1,
            ['associations' => $productAssociation1['associations']]
        )->shouldBeCalled();

        $updater->update(
            $product2,
            ['associations' => $productAssociation2['associations']]
        )->shouldBeCalled();

        $saver->saveAll([$product1, $product2])->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_product_does_not_exist(
        $fixtureReader,
        $productRepository,
        $updater,
        $saver,
        ProductInterface $product1
    ) {
        $productAssociation1 = [
            'identifier' => 'PLG566522',
            'associations' => [
                'SUBSTITUTION' => [
                    'products' => ['PLG566523', 'PLG570428'],
                    'product_models' => [],
                    'groups' => []
                ]
            ]
        ];

        $productAssociation2 = [
            'identifier' => 'unknown',
            'associations' => [
                'SUBSTITUTION' => [
                    'products' => ['11332477'],
                    'product_models' => [],
                    'groups' => []
                ]
            ]
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$productAssociation1, $productAssociation2]));

        $productRepository->findOneByIdentifier('PLG566522')->willReturn($product1);
        $productRepository->findOneByIdentifier('unknown')->willReturn(null);

        $updater->update(
            $product1,
            ['associations' => $productAssociation1['associations']]
        )->shouldBeCalled();

        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \Exception('Product "unknown" not found'))->during('install');
    }
}
