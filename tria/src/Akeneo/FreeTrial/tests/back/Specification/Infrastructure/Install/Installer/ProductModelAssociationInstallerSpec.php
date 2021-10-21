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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class ProductModelAssociationInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixtureReader,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith($fixtureReader, $productModelRepository, $updater, $saver);
    }

    public function it_successfully_installs_product_model_associations(
        $fixtureReader,
        $productModelRepository,
        $updater,
        $saver,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $productModelAssociation1 = [
            'code' => 'PLG395577',
            'associations' => [
                'X_SELL' => [
                    'products' => [],
                    'product_models' => ['PLGBEDROS'],
                    'groups' => []
                ]
            ]
        ];

        $productModelAssociation2 = [
            'code' => 'PLG01TOA',
            'associations' => [
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => ['PLG00121260'],
                    'groups' => []
                ]
            ]
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$productModelAssociation1, $productModelAssociation2]));

        $productModelRepository->findOneByIdentifier('PLG395577')->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier('PLG01TOA')->willReturn($productModel2);

        $updater->update(
            $productModel1,
            ['associations' => $productModelAssociation1['associations']]
        )->shouldBeCalled();

        $updater->update(
            $productModel2,
            ['associations' => $productModelAssociation2['associations']]
        )->shouldBeCalled();

        $saver->saveAll([$productModel1, $productModel2])->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_product_does_not_exist(
        $fixtureReader,
        $productModelRepository,
        $updater,
        $saver,
        ProductModelInterface $productModel1
    ) {
        $productModelAssociation1 = [
            'code' => 'PLG395577',
            'associations' => [
                'X_SELL' => [
                    'products' => [],
                    'product_models' => ['PLGBEDROS'],
                    'groups' => []
                ]
            ]
        ];

        $productModelAssociation2 = [
            'code' => 'unknown',
            'associations' => [
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => ['PLG00121260'],
                    'groups' => []
                ]
            ]
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$productModelAssociation1, $productModelAssociation2]));

        $productModelRepository->findOneByIdentifier('PLG395577')->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier('unknown')->willReturn(null);

        $updater->update(
            $productModel1,
            ['associations' => $productModelAssociation1['associations']]
        )->shouldBeCalled();

        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \Exception('Product model "unknown" not found'))->during('install');
    }
}
