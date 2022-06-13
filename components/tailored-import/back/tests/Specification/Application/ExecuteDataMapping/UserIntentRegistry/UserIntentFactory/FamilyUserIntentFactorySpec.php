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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\FamilyUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class FamilyUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid()
    {
        $propertyTarget = PropertyTarget::create(
            'categories',
            PropertyTarget::ACTION_SET,
            PropertyTarget::IF_EMPTY_CLEAR
        );
        $value = new ArrayValue(['t-shirt']);

        $this->shouldThrow(new \InvalidArgumentException('The target must be a PropertyTarget and be of type "family"'))
            ->during('create', [$propertyTarget, $value]);
    }

    public function it_creates_a_set_family_object()
    {
        $propertyTarget = PropertyTarget::create(
            'family',
            PropertyTarget::ACTION_SET,
            PropertyTarget::IF_EMPTY_CLEAR
        );

        $expected = new SetFamily('a_family');

        $this->create($propertyTarget, new StringValue('a_family'))->shouldBeLike($expected);
    }

    public function it_supports_target_type_family()
    {
        $propertyTarget = PropertyTarget::create(
            'family',
            PropertyTarget::ACTION_SET,
            PropertyTarget::IF_EMPTY_CLEAR
        );

        $this->supports($propertyTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_type()
    {
        $propertyTarget = PropertyTarget::create(
            'categories',
            PropertyTarget::ACTION_ADD,
            PropertyTarget::IF_EMPTY_CLEAR
        );

        $this->supports($propertyTarget)->shouldReturn(false);
    }
}
