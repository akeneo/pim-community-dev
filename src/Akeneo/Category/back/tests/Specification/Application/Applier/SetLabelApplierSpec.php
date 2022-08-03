<?php

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\SetLabelApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetLabelApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetLabelApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_label_user_intent(ObjectUpdaterInterface $updater): void
    {
        $category = new Category();
        $setLabel = new SetLabel('en_US', 'The label');

        $updater->update($category, [
            'labels' => [
                'en_US' => 'The label'
            ],
        ])->shouldBeCalledOnce();

        $this->apply($setLabel, $category, 1);
    }
}
