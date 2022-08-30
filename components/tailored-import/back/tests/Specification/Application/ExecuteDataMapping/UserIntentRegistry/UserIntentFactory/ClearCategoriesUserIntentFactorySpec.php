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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\ClearCategoriesUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class ClearCategoriesUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ClearCategoriesUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_clear_categories_user_intent(
        PropertyTarget $target,
    ) {
        $target->getCode()->willReturn('categories');

        $expected = new SetCategories([]);

        $this->create($target, new NullValue())->shouldBeLike($expected);
    }

    public function it_only_supports_categories_target_and_null_value(
        PropertyTarget $validTarget,
        PropertyTarget $invalidTarget,
    ) {
        $validTarget->getCode()->willReturn('categories');
        $invalidTarget->getCode()->willReturn('family');

        $validValue = new NullValue(['coucou']);
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
