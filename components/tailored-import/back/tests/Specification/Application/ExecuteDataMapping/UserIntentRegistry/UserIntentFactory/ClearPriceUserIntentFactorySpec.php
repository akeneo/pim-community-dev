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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\ClearPriceUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use PhpSpec\ObjectBehavior;

class ClearPriceUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ClearPriceUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_clear_price_user_intent(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_price_collection');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);
        $attributeTarget->getSourceConfiguration()->willReturn(['currency' => 'USD']);

        $expected = new ClearPriceValue('an_attribute_code', null, null, 'USD');

        $this->create($attributeTarget, new NullValue())->shouldBeLike($expected);
    }

    public function it_only_supports_price_target_and_null_value(
        AttributeTarget $validTarget,
        AttributeTarget $invalidTarget,
    ) {
        $validTarget->getAttributeType()->willReturn('pim_catalog_price_collection');
        $invalidTarget->getAttributeType()->willReturn('pim_catalog_number');

        $validValue = new NullValue();
        $invalidValue = new PriceValue('5', 'USD');

        $this->supports($validTarget, $validValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
