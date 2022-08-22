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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue as PriceValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\PriceUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use PhpSpec\ObjectBehavior;

class PriceUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PriceUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_textarea');
        $value = new PriceValue('123', 'EUR');

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_price_collection"'))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_create_a_set_price_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_price_collection');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetPriceValue(
            'an_attribute_code',
            null,
            null,
            new PriceValueUserIntent('12.5', 'EUR'),
        );

        $this->create($attributeTarget, new PriceValue('12.5', 'EUR'))->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_price_collection(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_price_collection');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_number');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
