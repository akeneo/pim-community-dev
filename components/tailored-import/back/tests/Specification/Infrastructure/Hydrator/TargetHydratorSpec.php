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
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetProperty;
use PhpSpec\ObjectBehavior;

class TargetHydratorSpec extends ObjectBehavior
{
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
        ], $indexedAttributes)->shouldBeLike(
            TargetAttribute::create(
                'name',
                'pim_catalog_text',
                null,
                null,
                'set',
                'skip',
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
            TargetProperty::create(
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
