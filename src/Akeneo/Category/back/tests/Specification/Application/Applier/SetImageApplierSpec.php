<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Applier\SetImageApplier;
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
class SetImageApplierSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SetImageApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_image_user_intent(): void
    {
        $compositeKey = 'attribute_code' . ValueCollection::SEPARATOR . 'uuid';
        $localeCompositeKey = 'attribute_code' . ValueCollection::SEPARATOR . 'uuid' . ValueCollection::SEPARATOR . 'locale_code';

        $attributes = ValueCollection::fromArray([
            'attribute_codes' => [$compositeKey],
            $localeCompositeKey => [
                'data' => 'value',
                'locale' => 'locale_code',
                'attribute_code' => $compositeKey
            ]
        ]);

        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            attributes: $attributes
        );

        $userIntent = new SetImage(
            'uuid',
            'attribute_code',
            'locale_code',
            [
                'size' => 168107,
                'extension' => 'jpg',
                'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                'mime_type' => 'image/jpeg',
                'original_filename' => 'shoes.jpg'
            ]
        );

        $expectedAttributes = ValueCollection::fromArray([
            'attribute_codes' => [$compositeKey],
            $localeCompositeKey => [
                'data' => [
                    'size' => 168107,
                    'extension' => 'jpg',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                    'mime_type' => 'image/jpeg',
                    'original_filename' => 'shoes.jpg'
                ],
                'locale' => 'locale_code',
                'attribute_code' => $compositeKey
            ]
        ]);

        $this->apply($userIntent, $category);

        Assert::assertEquals(
            $expectedAttributes,
            $category->getAttributes()
        );
    }

    function it_throws_exception_on_wrong_user_intent_applied(
        SetText $userIntent,
        Category $category
    ): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringApply($userIntent, $category);
    }
}
