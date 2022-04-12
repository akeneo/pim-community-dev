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

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\MeasurementUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use PhpSpec\ObjectBehavior;

class MeasurementUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_text');
        $value = '';

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_metric"'))->during('create', [$attributeTarget, $value]);
    }

    public function it_throws_an_exception_when_value_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_metric');
        $value = [];

        $this->shouldThrow(new \InvalidArgumentException('The value must be a string "array" given'))->during('create', [$attributeTarget, $value]);
    }

    public function it_creates_a_set_measurement_value_object(
        AttributeTarget $attributeTarget
    ) {
        $measurementSourceConfiguration = new MeasurementSourceConfiguration('METER', '.');

        $attributeTarget->getType()->willReturn('pim_catalog_metric');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);
        $attributeTarget->getSourceConfiguration()->willReturn($measurementSourceConfiguration);

        $expected = new SetMeasurementValue(
            'an_attribute_code',
            null,
            null,
            '123.5',
            'METER',
        );

        $this->create($attributeTarget, '123.5')->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_metric(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_metric');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_text');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
