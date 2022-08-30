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
use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
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

    public function it_creates_a_set_measurement_value_object(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_metric');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);
        $attributeTarget->getSourceConfiguration()->willReturn(['unit' => 'METER', 'decimal_separator' => '.']);

        $expected = new SetMeasurementValue(
            'an_attribute_code',
            null,
            null,
            '123.5',
            'METER',
        );

        $this->create($attributeTarget, new MeasurementValue('123.5', 'METER'))->shouldBeLike($expected);
    }

    public function it_only_supports_measurement_target_and_measurement_value(
        AttributeTarget $validTarget,
        AttributeTarget $invalidTarget,
    ) {
        $validTarget->getAttributeType()->willReturn('pim_catalog_metric');
        $invalidTarget->getAttributeType()->willReturn('pim_catalog_number');

        $validValue = new MeasurementValue('123.5', 'METER');
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
