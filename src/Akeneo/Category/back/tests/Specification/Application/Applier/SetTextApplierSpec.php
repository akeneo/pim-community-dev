<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Applier\SetTextApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
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
class SetTextApplierSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SetTextApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_text_user_intent(): void
    {
        $identifier = 'attribute_code' . ValueCollection::SEPARATOR . 'uuid';
        $valueKey = 'attribute_code'
            . ValueCollection::SEPARATOR . 'uuid' .
            ValueCollection::SEPARATOR . 'locale_code';

        $attributes = ValueCollection::fromArray(
            [
                'attribute_codes' => [$identifier],
                $valueKey => [
                    'data' => 'value',
                    'channel' => null,
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
                'attribute_codes' => [$identifier],
                $valueKey => [
                    'data' => 'updated_value',
                    'channel' => null,
                    'locale' => 'locale_code',
                    'attribute_code' => 'attribute_code' . ValueCollection::SEPARATOR . 'uuid'
                ]
            ]
        );

        $this->apply($userIntent, $category);
        Assert::assertEquals(
            $expectedAttributes,
            $category->getAttributes()
        );
    }

    function it_throws_exception_on_wrong_user_intent_applied(
        SetImage $userIntent,
        Category $category
    ): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringApply(
                $userIntent,
                $category
            );
    }
}
