<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Applier\SetImageApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
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
        $givenImageValue = ImageValue::fromApplier(
            value: [
                'size' => 168107,
                'extension' => 'jpg',
                'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                'mime_type' => 'image/jpeg',
                'original_filename' => 'shoes.jpg'
            ],
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'hero_banner',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $attributes = ValueCollection::fromArray([$givenImageValue]);

        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            attributes: $attributes
        );

        $userIntent = new SetImage(
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            attributeCode: 'hero_banner',
            channelCode: 'ecommerce',
            localeCode: 'en_US',
            value: [
                'size' => 168107,
                'extension' => 'png',
                'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.png',
                'mime_type' => 'image/png',
                'original_filename' => 'shoes.png'
            ]
        );

        $expectedAttributes = ValueCollection::fromArray([
            ImageValue::fromApplier(
                value: [
                    'size' => 168107,
                    'extension' => 'png',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.png',
                    'mime_type' => 'image/png',
                    'original_filename' => 'shoes.png'
                ],
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'hero_banner',
                channel: 'ecommerce',
                locale: 'en_US'
            )
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
