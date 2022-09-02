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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\CategoriesUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class CategoriesUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(CategoriesUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_set_categories_object()
    {
        $propertyTarget = PropertyTarget::create(
            'categories',
            PropertyTarget::ACTION_SET,
            PropertyTarget::IF_EMPTY_CLEAR
        );

        $expected = new SetCategories(
            ['a_category'],
        );

        $this->create($propertyTarget, new StringValue('a_category'))->shouldBeLike($expected);
        $this->create($propertyTarget, new ArrayValue(['a_category']))->shouldBeLike($expected);
    }

    public function it_creates_an_add_categories_object()
    {
        $propertyTarget = PropertyTarget::create(
            'categories',
            PropertyTarget::ACTION_ADD,
            PropertyTarget::IF_EMPTY_CLEAR
        );

        $expected = new AddCategories(
            ['a_category'],
        );

        $this->create($propertyTarget, new StringValue('a_category'))->shouldBeLike($expected);
        $this->create($propertyTarget, new ArrayValue(['a_category']))->shouldBeLike($expected);
    }

    public function it_only_supports_categories_target_and_array_and_string_values(
        PropertyTarget $validTarget,
        PropertyTarget $invalidTarget,
    ) {
        $validTarget->getCode()->willReturn('categories');
        $invalidTarget->getCode()->willReturn('family');

        $validValue = new ArrayValue(['coucou']);
        $anotherValidValue = new StringValue('coucou');
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);
        $this->supports($validTarget, $anotherValidValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);
        $this->supports($invalidTarget, $anotherValidValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
