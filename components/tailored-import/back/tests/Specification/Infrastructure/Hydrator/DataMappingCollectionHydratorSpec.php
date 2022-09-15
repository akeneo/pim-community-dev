<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\OperationCollectionHydrator;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\TargetHydrator;
use PhpSpec\ObjectBehavior;

class DataMappingCollectionHydratorSpec extends ObjectBehavior
{
    public function let(
        TargetHydrator $targetHydrator,
        OperationCollectionHydrator $operationCollectionHydrator,
    ) {
        $this->beConstructedWith($targetHydrator, $operationCollectionHydrator);
    }

    public function it_hydrates_a_data_mapping_collection(
        TargetHydrator $targetHydrator,
        OperationCollectionHydrator $operationCollectionHydrator,
    ) {
        $nameTarget = AttributeTarget::create(
            'name',
            'pim_catalog_text',
            null,
            null,
            'set',
            'skip',
            null,
        );

        $descriptionTarget = AttributeTarget::create(
            'description',
            'pim_catalog_text',
            'ecommerce',
            'fr_FR',
            'set',
            'skip',
            null,
        );

        $indexedAttributes = [
            'name' => new Attribute(
                'name',
                'pim_catalog_text',
                [],
                false,
                false,
                null,
                null,
                null,
                'text',
                [],
            ),
            'description' => new Attribute(
                'description',
                'pim_catalog_text',
                [],
                false,
                false,
                null,
                null,
                null,
                'text',
                [],
            ),
        ];

        $targetHydrator->hydrate($nameTarget->normalize(), $indexedAttributes)->willReturn($nameTarget);
        $targetHydrator->hydrate($descriptionTarget->normalize(), $indexedAttributes)->willReturn($descriptionTarget);

        $emptyOperationCollection = OperationCollection::create([]);
        $operationCollection = OperationCollection::create([
            new CleanHTMLOperation('00000000-0000-0000-0000-000000000000', [CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]),
        ]);

        $operationCollectionHydrator->hydrate($nameTarget->normalize(), [])->willReturn($emptyOperationCollection);
        $operationCollectionHydrator->hydrate($descriptionTarget->normalize(), [['type' => CleanHTMLOperation::TYPE]])->willReturn($operationCollection);

        $this->hydrate(
            [
                [
                    'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                    'target' => $nameTarget->normalize(),
                    'sources' => ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                    'operations' => [],
                    'sample_data' => [],
                ],
                [
                    'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                    'target' => $descriptionTarget->normalize(),
                    'sources' => ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                    'operations' => [
                        ['type' => CleanHTMLOperation::TYPE],
                    ],
                    'sample_data' => [],
                ],
            ],
            $indexedAttributes
        )->shouldBeLike(
            DataMappingCollection::create([
                DataMapping::create(
                    'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                    $nameTarget,
                    ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                    $emptyOperationCollection,
                    [],
                ),
                DataMapping::create(
                    'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                    $descriptionTarget,
                    ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                    $operationCollection,
                    [],
                ),
            ])
        );
    }
}
