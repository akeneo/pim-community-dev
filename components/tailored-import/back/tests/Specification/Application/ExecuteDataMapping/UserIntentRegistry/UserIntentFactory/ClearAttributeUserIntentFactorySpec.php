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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\ClearAttributeUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use PhpSpec\ObjectBehavior;

class ClearAttributeUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ClearAttributeUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_clear_value_user_intent(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_text');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new ClearValue('an_attribute_code', null, null);

        $this->create($attributeTarget, new NullValue())->shouldBeLike($expected);
    }

    public function it_only_supports_null_value(
        AttributeTarget $validTarget,
    ) {
        $validValue = new NullValue(false);
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);
        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
