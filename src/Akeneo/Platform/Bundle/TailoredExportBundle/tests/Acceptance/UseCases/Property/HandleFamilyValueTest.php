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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Property;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Family\FamilyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Family\FamilyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Family\InMemoryGetFamilyTranslations;
use PHPUnit\Framework\Assert;

final class HandleFamilyValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'family';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_family_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();
        $this->loadFamilyLabels();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $productMapper->map($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            [
                'operations' => [],
                'selection' => new FamilyCodeSelection(),
                'value' => new FamilyValue('pants'),
                'expected' => [self::TARGET_NAME => 'pants']
            ],
            [
                'operations' => [],
                'selection' => new FamilyLabelSelection('en_US'),
                'value' => new FamilyValue('pants'),
                'expected' => [self::TARGET_NAME => '[pants]']
            ],
            [
                'operations' => [],
                'selection' => new FamilyLabelSelection('fr_FR'),
                'value' => new FamilyValue('pants'),
                'expected' => [self::TARGET_NAME => 'Pantalons']
            ]
        ];
    }

    private function loadFamilyLabels()
    {
        /** @var InMemoryGetFamilyTranslations $familyLabelsRepository */
        $familyLabelsRepository = self::$container->get('akeneo.pim.structure.query.get_family_translations');
        $familyLabelsRepository->addFamilyLabel('pants', 'fr_FR', 'Pantalons');
    }
}
