<?php

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\SetLabelApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class SetLabelApplierSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SetLabelApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    public function it_applies_set_label_user_intent(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('my_category'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null);

        $setLabelEN = new SetLabel('en_US', 'The label');
        $setLabelFR = new SetLabel('fr_FR', 'Le label');
        $setLabelNewEN = new SetLabel('en_US', 'The new label');

        $this->apply($setLabelEN, $category);
        Assert::assertEquals('The label', $category->getLabels()->getTranslation('en_US'));

        $this->apply($setLabelFR, $category);
        Assert::assertEquals('The label', $category->getLabels()->getTranslation('en_US'));

        $this->apply($setLabelNewEN, $category);
        Assert::assertEquals('The new label', $category->getLabels()->getTranslation('en_US'));
    }
}
