<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Applier\SetImageApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetImageApplierSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SetImageApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_image_user_intent(): void
    {
        $valueKey = 'attribute_code'
            . ValueCollection::SEPARATOR . 'uuid' .
            ValueCollection::SEPARATOR . 'locale_code';

        $attributes = ValueCollection::fromArray(
            [
                $valueKey => [
                    'data' => 'value',
                    'locale' => 'locale_code',
                    'attribute_code' => 'attribute_code' . ValueCollection::SEPARATOR . 'uuid'
                ]
            ]
        );

        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            labels: LabelCollection::fromArray([]),
            attributes: $attributes
        );

        $userIntent = new SetText(
            'uuid',
            'attribute_code',
            'locale_code',
            'updated_value'
        );

        $expectedAttributes = ValueCollection::fromArray(
            [
                $valueKey => [
                    'data' => 'updated_value',
                    'locale' => 'locale_code',
                    'attribute_code' => 'attribute_code' . ValueCollection::SEPARATOR . 'uuid'
                ]
            ]
        );

        $expectedCategory = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            labels: LabelCollection::fromArray([]),
            attributes: $expectedAttributes
        );

        $this->apply($userIntent, $category);
        Assert::assertEquals(
            $expectedCategory->getAttributes(),
            $category->getAttributes()
        );
    }
}
