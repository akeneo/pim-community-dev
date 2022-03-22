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
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\TargetHydrator;
use PhpSpec\ObjectBehavior;

class DataMappingCollectionHydratorSpec extends ObjectBehavior
{
    public function let(TargetHydrator $targetHydrator)
    {
        $this->beConstructedWith($targetHydrator);
    }

    public function it_hydrates_an_data_mapping_collection(TargetHydrator $targetHydrator)
    {
        $nameTarget = TargetAttribute::create(
            'name',
            'pim_catalog_text',
            null,
            null,
            'set',
            'skip',
        );

        $descriptionTarget = TargetAttribute::create(
            'description',
            'pim_catalog_text',
            'ecommerce',
            'fr_FR',
            'set',
            'skip',
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
                []
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
                []
            ),
        ];

        $targetHydrator->hydrate([
            'type' => 'attribute',
            'code' => 'name',
            'channel' => null,
            'locale' => null,
            'action_if_not_empty' => 'set',
            'action_if_empty' => 'skip',
        ], $indexedAttributes)->willReturn($nameTarget);

        $targetHydrator->hydrate([
            'type' => 'attribute',
            'code' => 'description',
            'channel' => 'ecommerce',
            'locale' => 'fr_FR',
            'action_if_not_empty' => 'set',
            'action_if_empty' => 'skip',
        ], $indexedAttributes)->willReturn($descriptionTarget);

        $this->hydrate(
            [
                [
                    'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82feb',
                    'target' => [
                        'type' => 'attribute',
                        'code' => 'name',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['2d9e967a-5efa-4a31-a254-99f7c50a145c'],
                    'operations' => [],
                    'sample_data' => [],
                ],
                [
                    'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                    'target' => [
                        'type' => 'attribute',
                        'code' => 'description',
                        'channel' => 'ecommerce',
                        'locale' => 'fr_FR',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                    'operations' => [],
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
                    [],
                    [],
                ),
                DataMapping::create(
                    'b244c45c-d5ec-4993-8cff-7ccd04e82fec',
                    $descriptionTarget,
                    ['2d9e967a-4efa-4a31-a254-99f7c50a145c'],
                    [],
                    [],
                ),
            ])
        );
    }
}
