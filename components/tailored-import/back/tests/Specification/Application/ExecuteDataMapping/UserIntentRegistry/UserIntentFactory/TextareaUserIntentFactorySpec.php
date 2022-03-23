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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\TextareaUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use PhpSpec\ObjectBehavior;

class TextareaUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(TextareaUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_create_a_set_textarea_value_object(
        TargetAttribute $targetAttribute
    ) {
        $targetAttribute->getType()->willReturn('pim_catalog_text');
        $targetAttribute->getCode()->willReturn('an_attribute_code');
        $targetAttribute->getChannel()->willReturn(null);
        $targetAttribute->getLocale()->willReturn(null);

        $expectedSetNumberValue = new SetTextareaValue(
            'an_attribute_code',
            null,
            null,
            'a_value'
        );

        $this->create($targetAttribute, 'a_value')->shouldBeLike($expectedSetNumberValue);
    }

    public function it_supports_target_attribute_type_catalog_textarea(
        TargetAttribute $targetAttribute
    ) {
        $targetAttribute->getType()->willReturn('pim_catalog_textarea');

        $this->supports($targetAttribute)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        TargetAttribute $targetAttribute
    ) {
        $targetAttribute->getType()->willReturn('pim_catalog_number');

        $this->supports($targetAttribute)->shouldReturn(false);
    }
}
