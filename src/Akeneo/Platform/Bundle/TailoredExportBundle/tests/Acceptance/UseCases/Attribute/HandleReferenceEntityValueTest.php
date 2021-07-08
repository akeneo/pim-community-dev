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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordsLabelTranslations;
use PHPUnit\Framework\Assert;

final class HandleReferenceEntityValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_reference_entity_value(
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
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'starck']
            ],
            [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'Starck']
            ],
            [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('reference_entity_without_label'),
                'expected' => [self::TARGET_NAME => '[reference_entity_without_label]']
            ]
        ];
    }

    private function loadRecords()
    {
        /** @var InMemoryFindRecordsLabelTranslations $recordLabelsRepository */
        $recordLabelsRepository = self::$container->get('akeneo_referenceentity.infrastructure.persistence.query.enrich.find_records_labels_public_api');
        $recordLabelsRepository->addRecordLabel('designer', 'starck', 'en_US', 'Starck');
    }
}
