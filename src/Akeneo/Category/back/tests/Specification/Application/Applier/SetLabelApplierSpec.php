<?php

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\SetLabelApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class SetLabelApplierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SetLabelApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_label_user_intent(): void
    {
        $category = new Category(new CategoryId(1), new Code('my_category'), LabelCollection::fromArray([]), null);

        $setLabelEN = new SetLabel('en_US', 'The label');
        $setLabelFR = new SetLabel('fr_FR', 'Le label');
        $setLabelNewEN = new SetLabel('en_US', 'The new label');

        $this->apply($setLabelEN, $category);
        Assert::assertEquals('The label', $category->getLabelCollection()->getLabel('en_US'));

        $this->apply($setLabelFR, $category);
        Assert::assertEquals('The label', $category->getLabelCollection()->getLabel('en_US'));

        $this->apply($setLabelNewEN, $category);
        Assert::assertEquals('The new label', $category->getLabelCollection()->getLabel('en_US'));
    }
}
