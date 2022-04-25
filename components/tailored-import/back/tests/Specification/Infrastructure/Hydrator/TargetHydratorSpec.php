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
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\NumberSourceConfiguration;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\SourceConfigurationHydrator;
use PhpSpec\ObjectBehavior;

class TargetHydratorSpec extends ObjectBehavior
{
    public function let(SourceConfigurationHydrator $sourceConfigurationHydrator)
    {
        $this->beConstructedWith($sourceConfigurationHydrator);
    }

    public function it_hydrates_an_attribute_target()
    {
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

        $this->hydrate([
            'type' => 'attribute',
            'code' => 'name',
            'channel' => null,
            'locale' => null,
            'action_if_not_empty' => 'set',
            'action_if_empty' => 'skip',
            'source_configuration' => null,
        ], $indexedAttributes)->shouldBeLike(
            AttributeTarget::create(
                'name',
                'pim_catalog_text',
                null,
                null,
                'set',
                'skip',
                null,
            ),
        );
    }

    public function it_hydrates_an_attribute_and_a_source_configuration(
        SourceConfigurationHydrator $sourceConfigurationHydrator,
        NumberSourceConfiguration $numberSourceConfiguration,
    ) {
        $indexedAttributes = [
            'count' => new Attribute(
                'count',
                'pim_catalog_number',
                [],
                false,
                false,
                null,
                null,
                true,
                'number',
                []
            ),
        ];

        $sourceConfigurationHydrator->hydrate([
            'decimal_separator' => ','
        ], 'pim_catalog_number')->willReturn($numberSourceConfiguration);

        $this->hydrate([
            'type' => 'attribute',
            'code' => 'count',
            'channel' => null,
            'locale' => null,
            'action_if_not_empty' => 'set',
            'action_if_empty' => 'skip',
            'source_configuration' => [
                'decimal_separator' => ','
            ]
        ], $indexedAttributes)->shouldBeLike(
            AttributeTarget::create(
                'count',
                'pim_catalog_number',
                null,
                null,
                'set',
                'skip',
                $numberSourceConfiguration->getWrappedObject(),
            ),
        );
    }

    public function it_hydrates_an_property_target()
    {
        $this->hydrate([
            'type' => 'property',
            'code' => 'family',
            'action_if_not_empty' => 'set',
            'action_if_empty' => 'skip',
        ], [])->shouldBeLike(
            PropertyTarget::create(
                'family',
                'set',
                'skip',
            ),
        );
    }

    public function it_throws_an_error_when_attribute_target_is_not_found()
    {
        $this->shouldThrow(new \InvalidArgumentException('Attribute "unknown_attribute_code" does not exist'))->during(
            'hydrate',
            [
                [
                    'type' => 'attribute',
                    'code' => 'unknown_attribute_code',
                    'channel' => null,
                    'locale' => null,
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
                []
            ]
        );
    }

    public function it_throws_an_error_when_target_type_is_not_supported()
    {
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
        ];

        $this->shouldThrow(new \InvalidArgumentException('Unsupported "unknown_target_type" target type'))->during(
            'hydrate',
            [
                [
                    'type' => 'unknown_target_type',
                    'code' => 'name',
                    'channel' => null,
                    'locale' => null,
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
                $indexedAttributes
            ]
        );
    }
}
