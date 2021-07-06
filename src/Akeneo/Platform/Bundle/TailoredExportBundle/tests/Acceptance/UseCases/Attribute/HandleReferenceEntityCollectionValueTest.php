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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntityCollection\ReferenceEntityCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordsLabelTranslations;
use PHPUnit\Framework\Assert;

final class HandleReferenceEntityCollectionValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_reference_entity_collection_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();

        $this->loadRecords();

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
                'selection' => new ReferenceEntityCollectionCodeSelection(','),
                'value' => new ReferenceEntityCollectionValue(['blue', 'black']),
                'expected' => [self::TARGET_NAME => 'blue,black']
            ],
            [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionLabelSelection(',', 'en_US', 'color'),
                'value' => new ReferenceEntityCollectionValue(['blue', 'reference_entity_without_label']),
                'expected' => [self::TARGET_NAME => 'Blue,[reference_entity_without_label]']
            ],
        ];
    }

    private function loadRecords()
    {
        /** @var InMemoryFindRecordsLabelTranslations $recordLabelsRepository */
        $recordLabelsRepository = self::$container->get('akeneo_referenceentity.infrastructure.persistence.query.enrich.find_records_labels_public_api');
        $recordLabelsRepository->addRecordLabel('color', 'blue', 'en_US', 'Blue');
    }
}
