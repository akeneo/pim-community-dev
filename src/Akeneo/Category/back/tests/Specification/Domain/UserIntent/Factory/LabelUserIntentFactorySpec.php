<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class LabelUserIntentFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_manage_only_expected_field_names()
    {
        $this->getSupportedFieldNames()->shouldReturn(['labels']);
    }

    function it_creates_a_list_of_label_user_intents_based_on_labels_list()
    {
        $this->create(
            'labels',
            1,
            [
                'en_US' => 'sausages',
                'fr_FR' => 'saucisses'
            ]
        )->shouldBeLike([
            new SetLabel('en_US', 'sausages'),
            new SetLabel('fr_FR', 'saucisses'),
        ]);
    }

    function it_does_create_label_user_intent_with_null()
    {
        $this->create(
            'labels', 1, ['en_US' => null]
        )->shouldBeLike([
            new SetLabel('en_US', null),
        ]);
    }

    function it_throws_an_exception_when_data_has_wrong_format()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['labels', 1, null]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['labels', 1, 'data']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['labels', 1, true]);
    }
}
