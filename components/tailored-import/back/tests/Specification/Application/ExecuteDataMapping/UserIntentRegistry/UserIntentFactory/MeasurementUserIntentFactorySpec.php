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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\MeasurementUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
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
        TargetAttribute $targetAttribute
    ) {
        $measurementSourceParameter = new MeasurementSourceParameter('METER', '.');

        $targetAttribute->getType()->willReturn('pim_catalog_metric');
        $targetAttribute->getCode()->willReturn('an_attribute_code');
        $targetAttribute->getChannel()->willReturn(null);
        $targetAttribute->getLocale()->willReturn(null);
        $targetAttribute->getSourceParameter()->willReturn($measurementSourceParameter);

        $expected = new SetMetricValue(
            'an_attribute_code',
            null,
            null,
            '123.5',
            'METER',
        );

        $this->create($targetAttribute, '123.5')->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_metric(
        TargetAttribute $targetAttribute
    ) {
        $targetAttribute->getType()->willReturn('pim_catalog_metric');

        $this->supports($targetAttribute)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        TargetAttribute $targetAttribute
    ) {
        $targetAttribute->getType()->willReturn('pim_catalog_text');

        $this->supports($targetAttribute)->shouldReturn(false);
    }
}
